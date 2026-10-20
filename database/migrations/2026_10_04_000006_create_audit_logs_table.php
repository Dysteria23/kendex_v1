<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('action', 60);
            $table->string('entity', 60);
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('details')->nullable();
            $table->timestamp('created_at')->useCurrent(); // UTC (RNF-11)

            $table->index(['entity', 'entity_id']);
            $table->index(['user_id', 'created_at']);
        });

        // RN-10: el registro de auditoría no puede editarse ni eliminarse bajo ningún escenario.
        // Además de la protección en el modelo, PostgreSQL lo impone a nivel de base de datos.
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
                CREATE OR REPLACE FUNCTION kendex_block_audit_changes() RETURNS trigger AS $$
                BEGIN
                    RAISE EXCEPTION 'audit_logs es inalterable (RN-10)';
                END;
                $$ LANGUAGE plpgsql;

                CREATE TRIGGER audit_logs_immutable
                    BEFORE UPDATE OR DELETE ON audit_logs
                    FOR EACH ROW EXECUTE FUNCTION kendex_block_audit_changes();

                CREATE TRIGGER audit_logs_no_truncate
                    BEFORE TRUNCATE ON audit_logs
                    FOR EACH STATEMENT EXECUTE FUNCTION kendex_block_audit_changes();
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');

        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared('DROP FUNCTION IF EXISTS kendex_block_audit_changes()');
        }
    }
};
