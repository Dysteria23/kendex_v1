<?php

namespace App\Console\Commands;

use App\Models\Budget;
use App\Support\Dates;
use Illuminate\Console\Command;

class OpenMonth extends Command
{
    protected $signature = 'kendex:open-month';
    protected $description = 'Abre los presupuestos del mes actual copiando los montos asignados y cierra los períodos anteriores (RN-09, RF-54)';

    public function handle(): int
    {
        $now = Dates::now();
        $prev = $now->copy()->subMonthNoOverflow();

        $created = 0;

        Budget::where('year', $prev->year)->where('month', $prev->month)->get()->each(function (Budget $old) use ($now, &$created) {
            $new = Budget::firstOrCreate(
                ['department_id' => $old->department_id, 'category_id' => $old->category_id, 'year' => $now->year, 'month' => $now->month],
                ['amount_assigned' => $old->amount_assigned]
            );
            $created += $new->wasRecentlyCreated ? 1 : 0;
        });

        // Períodos anteriores al actual quedan inmutables.
        Budget::where('closed', false)
            ->where(fn ($q) => $q->where('year', '<', $now->year)
                ->orWhere(fn ($w) => $w->where('year', $now->year)->where('month', '<', $now->month)))
            ->update(['closed' => true]);

        $this->info("Presupuestos creados para " . Dates::period() . ": {$created}");

        return self::SUCCESS;
    }
}
