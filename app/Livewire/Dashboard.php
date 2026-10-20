<?php

namespace App\Livewire;

use App\Enums\ExpenseStatus;
use App\Enums\Role;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Department;
use App\Models\Expense;
use App\Models\User;
use App\Support\Dates;
use App\Support\ExpenseFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Panel')]
class Dashboard extends Component
{
    #[Url] public string $from = '';
    #[Url] public string $to = '';
    #[Url] public string $department_id = '';
    #[Url] public string $category_id = '';
    #[Url] public string $fiscal = '';
    #[Url] public int $months = 6;

    protected function base(User $user): Builder
    {
        return ExpenseFilter::apply(Expense::query()->visibleTo($user), [
            'from' => $this->from, 'to' => $this->to, 'department_id' => $this->department_id,
            'category_id' => $this->category_id, 'fiscal' => $this->fiscal,
        ]);
    }

    protected function palette(): array
    {
        return ['#0e5f63', '#14263a', '#b45309', '#2b7a9b', '#6b8e23', '#8a4f7d', '#b42318', '#5b6770'];
    }

    protected function compute(User $user): array
    {
        $spent = [ExpenseStatus::Approved->value, ExpenseStatus::Paid->value];
        $period = Dates::period();
        $prev = Dates::period(Dates::now()->subMonthNoOverflow());

        $committed = (float) $this->base($user)->where('budget_period', $period)->where('status', ExpenseStatus::Approved->value)->sum('amount');
        $executed = (float) $this->base($user)->where('budget_period', $period)->where('status', ExpenseStatus::Paid->value)->sum('amount');
        $previous = (float) $this->base($user)->where('budget_period', $prev)->whereIn('status', $spent)->sum('amount');

        $counters = $this->base($user)->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        $byDept = $this->base($user)->where('budget_period', $period)->whereIn('status', $spent)
            ->selectRaw('department_id, sum(amount) as total')->groupBy('department_id')->pluck('total', 'department_id');
        $deptNames = Department::whereIn('id', $byDept->keys())->pluck('name', 'id');

        $byCat = $this->base($user)->where('budget_period', $period)->whereIn('status', $spent)
            ->selectRaw('category_id, sum(amount) as total')->groupBy('category_id')->pluck('total', 'category_id');
        $catNames = Category::whereIn('id', $byCat->keys())->pluck('name', 'id');

        // Tendencia de los últimos 6 o 12 meses (RF-62)
        $months = in_array($this->months, [6, 12], true) ? $this->months : 6;
        $periods = collect(range($months - 1, 0))->map(fn ($i) => Dates::period(Dates::now()->subMonthsNoOverflow($i)));
        $trend = $this->base($user)->whereIn('status', $spent)->whereIn('budget_period', $periods)
            ->selectRaw('budget_period, sum(amount) as total')->groupBy('budget_period')->pluck('total', 'budget_period');

        $top = $this->base($user)->where('budget_period', $period)->whereIn('status', $spent)
            ->with('department:id,name')->orderByDesc('amount')->limit(5)->get()
            ->map(fn ($e) => ['id' => $e->id, 'number' => $e->number, 'description' => $e->description,
                'department' => $e->department->name, 'amount' => (float) $e->amount])->all();

        // Salud financiera por departamento (RF-60)
        $year = (int) substr($period, 0, 4);
        $month = (int) substr($period, 5, 2);
        $health = Budget::with('department:id,name')->whereNull('category_id')->where('year', $year)->where('month', $month)
            ->when(! in_array($user->role, [Role::Admin, Role::Finance], true), fn ($q) => $q->whereIn('department_id', $user->supervisedDepartmentIds()))
            ->when($this->department_id, fn ($q, $v) => $q->where('department_id', $v))
            ->get()->sortBy(fn ($b) => $b->department->name)
            ->map(fn ($b) => ['name' => $b->department->name, 'assigned' => (float) $b->amount_assigned,
                'committed' => (float) $b->amount_committed, 'executed' => (float) $b->amount_executed,
                'available' => $b->available, 'pct' => $b->used_percent])->values()->all();

        $colors = $this->palette();

        return [
            'period' => $period,
            'committed' => $committed,
            'executed' => $executed,
            'previous' => $previous,
            'counters' => [
                'pendiente' => (int) ($counters[ExpenseStatus::Pending->value] ?? 0),
                'aprobado' => (int) ($counters[ExpenseStatus::Approved->value] ?? 0),
                'rechazado' => (int) ($counters[ExpenseStatus::Rejected->value] ?? 0),
                'pagado' => (int) ($counters[ExpenseStatus::Paid->value] ?? 0),
            ],
            'top' => $top,
            'health' => $health,
            'deptChart' => [
                'type' => 'bar',
                'data' => ['labels' => $byDept->keys()->map(fn ($id) => $deptNames[$id] ?? '—')->values(),
                    'datasets' => [['label' => 'Gasto (RD$)', 'data' => $byDept->values()->map(fn ($v) => (float) $v), 'backgroundColor' => '#0e5f63']]],
                'options' => ['responsive' => true, 'maintainAspectRatio' => false, 'plugins' => ['legend' => ['display' => false]]],
            ],
            'catChart' => [
                'type' => 'doughnut',
                'data' => ['labels' => $byCat->keys()->map(fn ($id) => $catNames[$id] ?? '—')->values(),
                    'datasets' => [['data' => $byCat->values()->map(fn ($v) => (float) $v), 'backgroundColor' => array_slice($colors, 0, max(1, $byCat->count()))]]],
                'options' => ['responsive' => true, 'maintainAspectRatio' => false, 'plugins' => ['legend' => ['position' => 'bottom']]],
            ],
            'trendChart' => [
                'type' => 'line',
                'data' => ['labels' => $periods->values(),
                    'datasets' => [['label' => 'Gasto mensual (RD$)', 'data' => $periods->map(fn ($p) => (float) ($trend[$p] ?? 0))->values(),
                        'borderColor' => '#0e5f63', 'backgroundColor' => 'rgba(14,95,99,0.12)', 'fill' => true, 'tension' => 0.25]]],
                'options' => ['responsive' => true, 'maintainAspectRatio' => false, 'plugins' => ['legend' => ['display' => false]]],
            ],
        ];
    }

    public function render()
    {
        $user = auth()->user();
        $key = 'dash:' . $user->id . ':' . md5(json_encode([$this->from, $this->to, $this->department_id, $this->category_id, $this->fiscal, $this->months]));

        $data = Cache::tags(['metrics'])->remember($key, 60, fn () => $this->compute($user));

        return view('livewire.dashboard', $data + [
            'pendingReview' => Expense::query()->awaitingReview($user)->count(),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'canReview' => $user->role !== Role::Employee,
        ]);
    }
}
