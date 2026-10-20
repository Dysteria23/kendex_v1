<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('empleado')->index();
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->boolean('active')->default(true);
            $table->string('phone', 30)->nullable();
            $table->string('position')->nullable();
            $table->unsignedSmallInteger('failed_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('current_session_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn(['role', 'active', 'phone', 'position', 'failed_attempts', 'locked_until', 'last_login_at', 'current_session_id']);
        });
    }
};
