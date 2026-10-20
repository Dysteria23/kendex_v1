<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->decimal('budget_total', 14, 2)->nullable(); // tope acumulado del proyecto (RF-55)
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('category_id')->constrained('projects');
            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });
        Schema::dropIfExists('projects');
    }
};
