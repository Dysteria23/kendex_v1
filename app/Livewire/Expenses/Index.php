<?php

namespace App\Livewire\Expenses;

use App\Enums\ExpenseStatus;
use App\Models\Category;
use App\Models\Department;
use App\Models\Expense;
use App\Models\Project;
use App\Support\ExpenseFilter;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Solicitudes')]
class Index extends Component
{
    use WithPagination;

    #[Url] public string $search = '';
    #[Url] public string $status = '';
    #[Url] public string $category_id = '';
    #[Url] public string $department_id = '';
    #[Url] public string $project_id = '';
    #[Url] public string $from = '';
    #[Url] public string $to = '';
    #[Url] public string $min = '';
    #[Url] public string $max = '';
    #[Url] public string $fiscal = '';
    #[Url] public bool $review = false;

    public function updated(): void
    {
        $this->resetPage();
    }

    public function clear(): void
    {
        $this->reset(['search', 'status', 'category_id', 'department_id', 'project_id', 'from', 'to', 'min', 'max', 'fiscal', 'review']);
        $this->resetPage();
    }

    public function filters(): array
    {
        return array_filter([
            'search' => $this->search, 'status' => $this->status, 'category_id' => $this->category_id,
            'department_id' => $this->department_id, 'project_id' => $this->project_id, 'from' => $this->from, 'to' => $this->to,
            'min' => $this->min, 'max' => $this->max, 'fiscal' => $this->fiscal,
        ], fn ($v) => $v !== '' && $v !== null);
    }

    public function render()
    {
        $user = auth()->user();

        $query = Expense::query()->visibleTo($user)->with(['user:id,name', 'department:id,name', 'category:id,name']);

        if ($this->review) {
            $query->awaitingReview($user);
        }

        $expenses = ExpenseFilter::apply($query, $this->filters())->orderByDesc('id')->paginate(15);

        return view('livewire.expenses.index', [
            'expenses' => $expenses,
            'statuses' => ExpenseStatus::cases(),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'exportQuery' => $this->filters(),
        ]);
    }
}
