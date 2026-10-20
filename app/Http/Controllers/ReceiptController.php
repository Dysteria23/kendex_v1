<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

/** Entrega comprobantes solo a quien puede ver la solicitud. */
class ReceiptController extends Controller
{
    public function __invoke(Expense $expense, int $index)
    {
        Gate::authorize('view', $expense);

        $receipt = $expense->receipts()[$index] ?? abort(404);
        $disk = Storage::disk(config('kendex.receipts.disk'));

        abort_unless($disk->exists($receipt['path']), 404);

        return $disk->response($receipt['path'], $receipt['name'], [
            'Content-Type' => $receipt['mime'],
            'Content-Disposition' => 'inline; filename="' . $receipt['name'] . '"',
        ]);
    }
}
