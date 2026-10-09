<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\ProfileMenuItem;
use App\Models\SecurityCompany;
use App\Models\SecurityProfile;
use App\Models\SecurityUser;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SecurityViewsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_active_profile_assignments_are_exposed_to_power_bi_views(): void
    {
        $profile = SecurityProfile::query()->create([
            'codigo' => 'COMPRAS',
            'nombre' => 'Compras',
            'powerbi_role_name' => 'RLS_Compras',
            'activo' => true,
        ]);
        $securityUser = SecurityUser::query()->create([
            'correo' => 'persona@genz.com.py',
            'idperfil' => $profile->idperfil,
            'activo' => true,
        ]);
        $company = SecurityCompany::query()->create([
            'nombre' => 'Montreal',
            'clave_pbi' => 'MONTREAL',
            'activo' => true,
        ]);
        $securityUser->companyAssignments()->create([
            'idempresa' => $company->idempresa,
            'activo' => true,
        ]);
        $menuItem = MenuItem::query()->create([
            'nombre' => 'GENERAL',
            'pagina_destino' => 'Principal',
            'modulo' => 'GENERAL',
            'orden' => 1,
            'activo' => true,
            'requiere_permiso' => true,
            'es_predeterminado' => false,
        ]);
        ProfileMenuItem::query()->create([
            'idperfil' => $profile->idperfil,
            'idmenu' => $menuItem->idmenu,
            'activo' => true,
        ]);

        $connection = config('access-control.connection');
        $securityUserView = DB::connection($connection)->table('vw_pbi_security_users')->first();
        $companyView = DB::connection($connection)->table('vw_pbi_security_user_companies')->first();
        $menuPermissionView = DB::connection($connection)->table('vw_pbi_menu_permisos')->first();

        $this->assertSame('persona@genz.com.py', $securityUserView->Correo);
        $this->assertSame('COMPRAS', $securityUserView->PerfilCodigo);
        $this->assertSame('MONTREAL', $companyView->EmpresaClave);
        $this->assertSame($menuItem->idmenu, $menuPermissionView->idmenu);
    }
}
