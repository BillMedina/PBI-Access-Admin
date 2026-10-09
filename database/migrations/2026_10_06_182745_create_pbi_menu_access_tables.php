<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = $this->connectionName();
        $schema = Schema::connection($connection);

        if (! $schema->hasTable('pbi_menu_items')) {
            $schema->create('pbi_menu_items', function (Blueprint $table): void {
                $table->increments('idmenu');
                $table->string('nombre', 100);
                $table->string('pagina_destino', 255);
                $table->string('modulo', 50)->default('GENERAL');
                $table->unsignedInteger('orden');
                $table->boolean('activo')->default(true);
                $table->boolean('requiere_permiso')->default(true);
                $table->boolean('es_predeterminado')->default(false);
                $table->timestamp('fecha_creacion')->useCurrent();
                $table->string('usuario_creacion', 255)->nullable();
                $table->timestamp('fecha_actualizacion')->nullable();
                $table->string('usuario_actualizacion', 255)->nullable();
                $table->timestamp('fecha_eliminacion')->nullable();
                $table->string('usuario_eliminacion', 255)->nullable();

                $table->index(['modulo', 'activo', 'orden'], 'pbi_menu_items_listing_idx');
            });
        }

        if (! $schema->hasTable('pbi_menu_user_permissions')) {
            $schema->create('pbi_menu_user_permissions', function (Blueprint $table): void {
                $table->bigIncrements('idmenu_permiso');
                $table->string('correo', 255);
                $table->unsignedInteger('idmenu');
                $table->boolean('activo')->default(true);
                $table->timestamp('fecha_creacion')->useCurrent();
                $table->string('usuario_creacion', 255)->nullable();
                $table->timestamp('fecha_actualizacion')->nullable();
                $table->string('usuario_actualizacion', 255)->nullable();
                $table->timestamp('fecha_eliminacion')->nullable();
                $table->string('usuario_eliminacion', 255)->nullable();

                $table->unique(['correo', 'idmenu'], 'pbi_menu_user_permissions_unique');
                $table->index(['correo', 'activo'], 'pbi_menu_user_permissions_email_idx');
                $table->index(['idmenu', 'activo'], 'pbi_menu_user_permissions_menu_idx');
                $table->foreign('idmenu', 'pbi_menu_user_permissions_menu_fk')
                    ->references('idmenu')
                    ->on('pbi_menu_items')
                    ->restrictOnDelete();
            });
        }

        if (! $schema->hasTable('pbi_access_audit_logs')) {
            $schema->create('pbi_access_audit_logs', function (Blueprint $table): void {
                $table->bigIncrements('id_auditoria');
                $table->string('entidad', 100);
                $table->string('entidad_id', 100);
                $table->string('accion', 50);
                $table->json('estado_anterior')->nullable();
                $table->json('estado_posterior')->nullable();
                $table->string('usuario_admin', 255);
                $table->string('ip_address', 45)->nullable();
                $table->string('agente_usuario', 255)->nullable();
                $table->timestamp('fecha_evento')->useCurrent();

                $table->index(['entidad', 'entidad_id'], 'pbi_access_audit_logs_entity_idx');
                $table->index('fecha_evento', 'pbi_access_audit_logs_date_idx');
            });
        }

        $this->replaceViews($connection);
    }

    public function down(): void
    {
        $connection = $this->connectionName();
        $schema = Schema::connection($connection);

        $this->dropViews($connection);
        $schema->dropIfExists('pbi_access_audit_logs');
        $schema->dropIfExists('pbi_menu_user_permissions');
        $schema->dropIfExists('pbi_menu_items');
    }

    private function connectionName(): string
    {
        $connection = config('access-control.connection');

        return is_string($connection) ? $connection : 'pbi';
    }

    private function replaceViews(string $connection): void
    {
        $database = DB::connection($connection);

        $this->dropViews($connection);

        $database->unprepared(<<<'SQL'
CREATE VIEW vw_pbi_menu AS
SELECT
    idmenu,
    nombre,
    pagina_destino,
    orden,
    modulo,
    es_predeterminado
FROM pbi_menu_items
WHERE activo = 1
  AND fecha_eliminacion IS NULL
SQL);

        $database->unprepared(<<<'SQL'
CREATE VIEW vw_pbi_menu_permisos AS
SELECT DISTINCT
    LOWER(TRIM(permission.correo)) AS Correo,
    permission.idmenu
FROM pbi_menu_user_permissions AS permission
INNER JOIN pbi_menu_items AS menu_item
    ON menu_item.idmenu = permission.idmenu
WHERE permission.activo = 1
  AND permission.fecha_eliminacion IS NULL
  AND menu_item.activo = 1
  AND menu_item.fecha_eliminacion IS NULL
  AND menu_item.requiere_permiso = 1
SQL);
    }

    private function dropViews(string $connection): void
    {
        $database = DB::connection($connection);

        $database->unprepared('DROP VIEW IF EXISTS vw_pbi_menu_permisos');
        $database->unprepared('DROP VIEW IF EXISTS vw_pbi_menu');
    }
};
