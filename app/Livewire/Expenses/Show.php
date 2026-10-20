<?php

namespace App\Livewire\Expenses;

use App\Enums\PaymentMethod;
use App\Models\Expense;
use App\Services\ExpenseService;
use App\Support\Dates;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Detalle de solicitud')]
class Show extends Component
{
    public Expense $expense;

    public string $comment = '';
    public string $method = '';
    public string $reference = '';
    public string $paid_at = '';

    public function mount(Expense $expense): void
    {
        Gate::authorize('view', $expense);

        $this->expense = $expense;
        $this->paid_at = Dates::today();
    }

    protected function done(string $message): void
    {
        $this->expense->refresh();
        $this->reset(['comment', 'reference', 'method']);
        $this->paid_at = Dates::today();
        session()->flash('status', $message);
    }

    public function approve(ExpenseService $service): void
    {
        $expense = $service->approve(auth()->user(), $this->expense, $this->comment ?: null);

        $this->done($expense->status->value === 'aprobado'
            ? 'Solicitud aprobada.'
            : 'Primera aprobación registrada. Falta la aprobación del administrador.');
    }

    public function reject(ExpenseService $service): void
    {
        $service->reject(auth()->user(), $this->expense, $this->comment);
        $this->done('Solicitud rechazada.');
    }

    public function requestChanges(ExpenseService $service): void
    {
        $service->requestChanges(auth()->user(), $this->expense, $this->comment);
        $this->done('Se pidió al empleado que modifique la solicitud.');
    }

    public function addComment(ExpenseService $service): void
    {
        $service->comment(auth()->user(), $this->expense, $this->comment);
        $this->done('Comentario agregado.');
    }

    public function cancel(ExpenseService $service): void
    {
        $service->cancel(auth()->user(), $this->expense);
        $this->done('Solicitud anulada.');
    }

    public function pay(ExpenseService $service): void
    {
        $this->validate([
            'method' => ['required', 'in:' . implode(',', array_column(PaymentMethod::cases(), 'value'))],
            'paid_at' => ['required', 'date', 'before_or_equal:' . Dates::today()],
        ], [], ['method' => 'método de pago', 'paid_at' => 'fecha de pago']);

        $service->pay(auth()->user(), $this->expense, [
            'method' => $this->method, 'reference' => $this->reference, 'paid_at' => $this->paid_at,
        ]);

        $this->done('Pago registrado.');
    }

    public function render()
    {
        $this->expense->load(['user', 'department', 'category.parent', 'project', 'approvals.user', 'payment.recorder']);
        $user = auth()->user();

        return view('livewire.expenses.show', [
            'canUpdate' => $user->can('update', $this->expense),
            'canCancel' => $user->can('cancel', $this->expense),
            'canReview' => $user->can('review', $this->expense),
            'canComment' => $user->can('comment', $this->expense),
            'canPay' => $user->can('pay', $this->expense),
            'methods' => PaymentMethod::cases(),
        ]);
    }
}
