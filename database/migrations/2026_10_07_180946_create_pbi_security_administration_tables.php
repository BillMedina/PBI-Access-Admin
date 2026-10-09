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

        if (! $schema->hasTable('pbi_security_profiles')) {
            $schema->create('pbi_security_profiles', function (Blueprint $table): void {
                $table->bigIncrements('idperfil');
                $table->string('codigo', 50)->unique();
                $table->string('nombre', 100)->unique();
                $table->string('powerbi_role_name', 100)->unique();
                $table->boolean('activo')->default(true);
                $table->timestamp('fecha_creacion')->useCurrent();
                $table->string('usuario_creacion', 255)->nullable();
                $table->timestamp('fecha_actualizacion')->nullable();
                $table->string('usuario_actualizacion', 255)->nullable();
                $table->timestamp('fecha_eliminacion')->nullable();
                $table->string('usuario_eliminacion', 255)->nullable();

                $table->index('activo', 'pbi_security_profiles_active_idx');
            });
        }

        if (! $schema->hasTable('pbi_security_users')) {
            $schema->create('pbi_security_users', function (Blueprint $table): void {
                $table->bigIncrements('idusuario');
                $table->string('nombre', 150)->nullable();
                $table->string('correo', 255)->unique();
                $table->unsignedBigInteger('idperfil');
                $table->boolean('activo')->default(true);
                $table->timestamp('fecha_creacion')->useCurrent();
                $table->string('usuario_creacion', 255)->nullable();
                $table->timestamp('fecha_actualizacion')->nullable();
                $table->string('usuario_actualizacion', 255)->nullable();
                $table->timestamp('fecha_eliminacion')->nullable();
                $table->string('usuario_eliminacion', 255)->nullable();

                $table->index(['idperfil', 'activo'], 'pbi_security_users_profile_idx');
                $table->foreign('idperfil', 'pbi_security_users_profile_fk')
                    ->references('idperfil')
                    ->on('pbi_security_profiles')
                    ->restrictOnDelete();
            });
        }

        if (! $schema->hasTable('pbi_security_companies')) {
            $schema->create('pbi_security_companies', function (Blueprint $table): void {
                $table->bigIncrements('idempresa');
                $table->string('nombre', 150)->unique();
                $table->string('clave_pbi', 255)->unique();
                $table->boolean('activo')->default(true);
                $table->timestamp('fecha_creacion')->useCurrent();
                $table->string('usuario_creacion', 255)->nullable();
                $table->timestamp('fecha_actualizacion')->nullable();
                $table->string('usuario_actualizacion', 255)->nullable();
                $table->timestamp('fecha_eliminacion')->nullable();
                $table->string('usuario_eliminacion', 255)->nullable();

                $table->index('activo', 'pbi_security_companies_active_idx');
            });
        }

        if (! $schema->hasTable('pbi_security_user_companies')) {
            $schema->create('pbi_security_user_companies', function (Blueprint $table): void {
                $table->bigIncrements('idusuario_empresa');
                $table->unsignedBigInteger('idusuario');
                $table->unsignedBigInteger('idempresa');
                $table->boolean('activo')->default(true);
                $table->timestamp('fecha_creacion')->useCurrent();
                $table->string('usuario_creacion', 255)->nullable();
                $table->timestamp('fecha_actualizacion')->nullable();
                $table->string('usuario_actualizacion', 255)->nullable();
                $table->timestamp('fecha_eliminacion')->nullable();
                $table->string('usuario_eliminacion', 255)->nullable();

                $table->unique(['idusuario', 'idempresa'], 'pbi_security_user_company_unique');
                $table->index(['idusuario', 'activo'], 'pbi_security_user_company_user_idx');
                $table->index(['idempresa', 'activo'], 'pbi_security_user_company_company_idx');
                $table->foreign('idusuario', 'pbi_security_user_company_user_fk')
                    ->references('idusuario')
                    ->on('pbi_security_users')
                    ->restrictOnDelete();
                $table->foreign('idempresa', 'pbi_security_user_company_company_fk')
                    ->references('idempresa')
                    ->on('pbi_security_companies')
                    ->restrictOnDelete();
            });
        }

        if (! $schema->hasTable('pbi_security_profile_menu_items')) {
            $schema->create('pbi_security_profile_menu_items', function (Blueprint $table): void {
                $table->bigIncrements('idperfil_menu');
                $table->unsignedBigInteger('idperfil');
                $table->unsignedInteger('idmenu');
                $table->boolean('activo')->default(true);
                $table->timestamp('fecha_creacion')->useCurrent();
                $table->string('usuario_creacion', 255)->nullable();
                $table->timestamp('fecha_actualizacion')->nullable();
                $table->string('usuario_actualizacion', 255)->nullable();
                $table->timestamp('fecha_eliminacion')->nullable();
                $table->string('usuario_eliminacion', 255)->nullable();

                $table->unique(['idperfil', 'idmenu'], 'pbi_security_profile_menu_unique');
                $table->index(['idperfil', 'activo'], 'pbi_security_profile_menu_profile_idx');
                $table->foreign('idperfil', 'pbi_security_profile_menu_profile_fk')
                    ->references('idperfil')
                    ->on('pbi_security_profiles')
                    ->restrictOnDelete();
                $table->foreign('idmenu', 'pbi_security_profile_menu_item_fk')
                    ->references('idmenu')
                    ->on('pbi_menu_items')
                    ->restrictOnDelete();
            });
        }

        $this->replaceViews($connection);
    }

    public function down(): void
    {
        $connection = $this->connectionName();
        $schema = Schema::connection($connection);

        $this->dropSecurityViews($connection);
        $this->restoreDirectMenuPermissionsView($connection);
        $schema->dropIfExists('pbi_security_profile_menu_items');
        $schema->dropIfExists('pbi_security_user_companies');
        $schema->dropIfExists('pbi_security_companies');
        $schema->dropIfExists('pbi_security_users');
        $schema->dropIfExists('pbi_security_profiles');
    }

    private function connectionName(): string
    {
        $connection = config('access-control.connection');

        return is_string($connection) ? $connection : 'pbi';
    }

    private function replaceViews(string $connection): void
    {
        $database = DB::connection($connection);

        $this->dropSecurityViews($connection);
        $database->unprepared('DROP VIEW IF EXISTS vw_pbi_menu_permisos');

        $database->unprepared(<<<'SQL'
CREATE VIEW vw_pbi_security_users AS
SELECT
    LOWER(TRIM(security_user.correo)) AS Correo,
    security_profile.codigo AS PerfilCodigo,
    security_profile.nombre AS PerfilNombre,
    security_profile.powerbi_role_name AS PowerBiRole
FROM pbi_security_users AS security_user
INNER JOIN pbi_security_profiles AS security_profile
    ON security_profile.idperfil = security_user.idperfil
WHERE security_user.activo = 1
  AND security_user.fecha_eliminacion IS NULL
  AND security_profile.activo = 1
  AND security_profile.fecha_eliminacion IS NULL
SQL);

        $database->unprepared(<<<'SQL'
CREATE VIEW vw_pbi_security_user_companies AS
SELECT DISTINCT
    LOWER(TRIM(security_user.correo)) AS Correo,
    security_company.clave_pbi AS EmpresaClave
FROM pbi_security_user_companies AS user_company
INNER JOIN pbi_security_users AS security_user
    ON security_user.idusuario = user_company.idusuario
INNER JOIN pbi_security_profiles AS security_profile
    ON security_profile.idperfil = security_user.idperfil
INNER JOIN pbi_security_companies AS security_company
    ON security_company.idempresa = user_company.idempresa
WHERE user_company.activo = 1
  AND user_company.fecha_eliminacion IS NULL
  AND security_user.activo = 1
  AND security_user.fecha_eliminacion IS NULL
  AND security_profile.activo = 1
  AND security_profile.fecha_eliminacion IS NULL
  AND security_company.activo = 1
  AND security_company.fecha_eliminacion IS NULL
SQL);

        $database->unprepared(<<<'SQL'
CREATE VIEW vw_pbi_menu_permisos AS
SELECT DISTINCT
    LOWER(TRIM(security_user.correo)) AS Correo,
    profile_menu_item.idmenu
FROM pbi_security_users AS security_user
INNER JOIN pbi_security_profiles AS security_profile
    ON security_profile.idperfil = security_user.idperfil
INNER JOIN pbi_security_profile_menu_items AS profile_menu_item
    ON profile_menu_item.idperfil = security_profile.idperfil
INNER JOIN pbi_menu_items AS menu_item
    ON menu_item.idmenu = profile_menu_item.idmenu
WHERE security_user.activo = 1
  AND security_user.fecha_eliminacion IS NULL
  AND security_profile.activo = 1
  AND security_profile.fecha_eliminacion IS NULL
  AND profile_menu_item.activo = 1
  AND profile_menu_item.fecha_eliminacion IS NULL
  AND menu_item.activo = 1
  AND menu_item.fecha_eliminacion IS NULL
  AND menu_item.requiere_permiso = 1
UNION
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

    private function dropSecurityViews(string $connection): void
    {
        $database = DB::connection($connection);

        $database->unprepared('DROP VIEW IF EXISTS vw_pbi_security_user_companies');
        $database->unprepared('DROP VIEW IF EXISTS vw_pbi_security_users');
    }

    private function restoreDirectMenuPermissionsView(string $connection): void
    {
        $database = DB::connection($connection);

        $database->unprepared('DROP VIEW IF EXISTS vw_pbi_menu_permisos');
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
};
