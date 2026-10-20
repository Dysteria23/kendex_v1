<?php

namespace App\Livewire\Expenses;

use App\Models\Category;
use App\Models\Department;
use App\Models\Expense;
use App\Models\Project;
use App\Services\ExpenseService;
use App\Support\Dates;
use App\Support\Money;
use App\Support\Settings;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Solicitud de gasto')]
class Form extends Component
{
    use WithFileUploads;

    public ?Expense $expense = null;

    public string $category_id = '';
    public string $department_id = '';
    public string $project_id = '';
    public string $amount = '';
    public string $itbis_amount = '0';
    public string $rnc_provider = '';
    public string $ncf = '';
    public string $description = '';
    public string $expense_date = '';

    /** Comprobantes nuevos pendientes de guardar */
    public array $uploads = [];
    public $upload = null;

    /** Comprobantes ya guardados que se conservan al editar */
    public array $existing = [];

    public function mount(?Expense $expense = null): void
    {
        $user = auth()->user();

        if ($expense?->exists) {
            Gate::authorize('update', $expense);

            $this->expense = $expense;
            $this->category_id = (string) $expense->category_id;
            $this->department_id = (string) $expense->department_id;
            $this->project_id = (string) $expense->project_id;
            $this->amount = (string) $expense->amount;
            $this->itbis_amount = (string) $expense->itbis_amount;
            $this->rnc_provider = (string) $expense->rnc_provider;
            $this->ncf = (string) $expense->ncf;
            $this->description = $expense->description;
            $this->expense_date = $expense->expense_date->toDateString();
            $this->existing = $expense->receipts();
        } else {
            $this->department_id = (string) $user->department_id;
            $this->expense_date = Dates::today();
        }
    }

    public function updatedUpload(): void
    {
        $this->validateOnly('upload', [
            'upload' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:' . config('kendex.receipts.max_kb')],
        ]);

        $this->uploads[] = $this->upload;
        $this->upload = null;
        $this->resetErrorBag('uploads');
    }

    public function removeUpload(int $index): void
    {
        unset($this->uploads[$index]);
        $this->uploads = array_values($this->uploads);
    }

    public function removeExisting(int $index): void
    {
        unset($this->existing[$index]);
        $this->existing = array_values($this->existing);
    }

    protected function rules(): array
    {
        $rules = [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('active', true)],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')->where('active', true)],
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')->where('active', true)],
            'amount' => ['required', 'numeric', 'gt:0', 'max:99999999.99'], // RN-02
            'itbis_amount' => ['nullable', 'numeric', 'gte:0', 'lte:amount'],
            'rnc_provider' => ['nullable', 'regex:/^(\d{9}|\d{11})$/'],
            'ncf' => [Rule::requiredIf(filled($this->rnc_provider)), 'nullable', 'regex:/^(B\d{10}|E\d{12})$/'],
            'description' => ['required', 'string', 'min:5', 'max:500'],
            'expense_date' => ['required', 'date', 'before_or_equal:' . Dates::today()], // RN-03
        ];

        $maxAge = (int) Settings::get('max_age_days');
        if ($maxAge > 0) {
            $rules['expense_date'][] = 'after_or_equal:' . Dates::now()->subDays($maxAge)->toDateString();
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'rnc_provider.regex' => 'El RNC debe tener 9 dígitos o la cédula 11 dígitos, sin guiones.',
            'ncf.regex' => 'El NCF debe tener el formato B0100000001 (11 caracteres) o E310000000001 (13 caracteres).',
            'ncf.required' => 'Indica el NCF de la factura cuando registras el RNC del proveedor.',
            'itbis_amount.lte' => 'El ITBIS no puede ser mayor que el monto total.',
            'expense_date.before_or_equal' => 'La fecha del gasto no puede ser futura.',
            'expense_date.after_or_equal' => 'Solo se aceptan gastos de los últimos ' . (int) Settings::get('max_age_days') . ' días.',
        ];
    }

    protected function normalize(): void
    {
        $this->amount = str_replace([',', ' '], '', $this->amount);
        $this->itbis_amount = str_replace([',', ' '], '', $this->itbis_amount) ?: '0';
        $this->rnc_provider = preg_replace('/\D/', '', $this->rnc_provider);
        $this->ncf = strtoupper(str_replace([' ', '-'], '', $this->ncf));
    }

    public function save(ExpenseService $service)
    {
        $this->normalize();
        $data = $this->validate();

        // RN-01 / RN-04: comprobante obligatorio sobre el umbral configurado.
        $threshold = (float) Settings::get('receipt_threshold');
        if ((float) $data['amount'] > $threshold && count($this->existing) + count($this->uploads) === 0) {
            $this->addError('uploads', 'Adjunta al menos un comprobante: es obligatorio para montos mayores a ' . Money::format($threshold) . '.');

            return;
        }

        $user = auth()->user();
        $files = $this->uploads;

        $expense = $this->expense?->exists
            ? $service->update($user, $this->expense, $data, $files, $this->existing)
            : $service->create($user, $data, $files);

        session()->flash('status', "Solicitud {$expense->number} guardada.");

        return $this->redirectRoute('expenses.show', $expense, navigate: true);
    }

    public function render()
    {
        return view('livewire.expenses.form', [
            'categories' => Category::where('active', true)->with('parent')->orderBy('name')->get(),
            'departments' => Department::where('active', true)->orderBy('name')->get(),
            'projects' => Project::where('active', true)
                ->where(fn ($q) => $q->whereNull('department_id')->orWhere('department_id', $this->department_id ?: 0))
                ->orderBy('name')->get(),
            'isAdmin' => auth()->user()->isAdmin(),
            'threshold' => (float) Settings::get('receipt_threshold'),
            'editing' => (bool) $this->expense?->exists,
        ]);
    }
}
