<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('number')->nullable()->unique(); // correlativo GS-AAAA-000001 (RF-22)
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('category_id')->constrained('categories');
            $table->decimal('amount', 14, 2);
            $table->decimal('subtotal', 14, 2);
            $table->decimal('itbis_amount', 14, 2)->default(0);
            $table->string('rnc_provider', 11)->nullable();
            $table->string('ncf', 13)->nullable();
            $table->text('description');
            $table->date('expense_date');
            $table->string('status', 30)->default('pendiente')->index();
            $table->string('approval_stage', 20)->default('supervisor'); // supervisor | admin
            $table->json('receipt_path')->nullable();
            $table->string('budget_period', 7)->nullable()->index(); // YYYY-MM al aprobar
            $table->timestamp('last_reminded_at')->nullable();
            $table->timestamps();

            $table->index(['department_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('expense_date');
        });

        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('expenses');
            $table->foreignId('user_id')->constrained('users');
            $table->string('action', 20); // approve, reject, modify, cancel, comment, resubmit, pay
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->unique()->constrained('expenses');
            $table->string('method', 30);
            $table->string('reference')->nullable();
            $table->date('paid_at');
            $table->foreignId('recorded_by')->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('expenses');
    }
};
