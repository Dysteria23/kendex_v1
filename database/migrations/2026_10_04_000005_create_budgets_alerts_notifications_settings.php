<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('category_id')->nullable()->constrained('categories');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('amount_assigned', 14, 2)->default(0);
            $table->decimal('amount_committed', 14, 2)->default(0);
            $table->decimal('amount_executed', 14, 2)->default(0);
            $table->boolean('closed')->default(false); // períodos cerrados inmutables (RF-54)
            $table->timestamps();

            $table->unique(['department_id', 'category_id', 'year', 'month']);
            $table->index(['year', 'month']);
        });

        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments');
            $table->string('type', 20); // preventiva | advertencia | critica
            $table->unsignedSmallInteger('threshold');
            $table->string('period', 7);
            $table->text('message');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['department_id', 'period', 'threshold']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('type', 40);
            $table->text('message');
            $table->string('url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'read_at']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('budgets');
    }
};
