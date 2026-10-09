<?php

namespace Tests\Feature;

use App\Models\SecurityCompany;
use App\Models\SecurityProfile;
use App\Models\SecurityUser;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SecurityUserControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_administrator_assigns_one_profile_and_multiple_companies_to_a_user(): void
    {
        $profile = $this->createProfile();
        $montreal = $this->createCompany('Montreal', 'MONTREAL');
        $atlantico = $this->createCompany('Atlántico', 'ATLANTICO');

        $response = $this->asPbiAdministrator()->post(route('security-users.store'), [
            'nombre' => 'Persona de Prueba',
            'correo' => 'Persona@GENZ.COM.PY',
            'idperfil' => $profile->idperfil,
            'company_ids' => [$montreal->idempresa, $atlantico->idempresa],
            'activo' => true,
        ]);

        $response->assertRedirect(route('security-users.index'));
        $this->assertDatabaseHas('pbi_security_users', [
            'nombre' => 'Persona de Prueba',
            'correo' => 'persona@genz.com.py',
            'idperfil' => $profile->idperfil,
            'activo' => 1,
        ]);

        $securityUser = SecurityUser::query()->where('correo', 'persona@genz.com.py')->firstOrFail();
        $this->assertDatabaseHas('pbi_security_user_companies', [
            'idusuario' => $securityUser->idusuario,
            'idempresa' => $montreal->idempresa,
            'activo' => 1,
        ]);
        $this->assertDatabaseHas('pbi_security_user_companies', [
            'idusuario' => $securityUser->idusuario,
            'idempresa' => $atlantico->idempresa,
            'activo' => 1,
        ]);
        $this->assertDatabaseHas('pbi_access_audit_logs', [
            'entidad' => 'pbi_security_users',
            'accion' => 'created',
            'usuario_admin' => 'admin',
        ]);
    }

    public function test_updating_user_companies_revokes_removed_assignments_logically(): void
    {
        $profile = $this->createProfile();
        $montreal = $this->createCompany('Montreal', 'MONTREAL');
        $atlantico = $this->createCompany('Atlántico', 'ATLANTICO');
        $securityUser = SecurityUser::query()->create([
            'correo' => 'persona@genz.com.py',
            'idperfil' => $profile->idperfil,
            'activo' => true,
        ]);
        $securityUser->companyAssignments()->create([
            'idempresa' => $montreal->idempresa,
            'activo' => true,
        ]);

        $response = $this->asPbiAdministrator()->put(route('security-users.update', $securityUser), [
            'nombre' => '',
            'correo' => 'persona@genz.com.py',
            'idperfil' => $profile->idperfil,
            'company_ids' => [$atlantico->idempresa],
            'activo' => true,
        ]);

        $response->assertRedirect(route('security-users.index'));
        $this->assertDatabaseHas('pbi_security_user_companies', [
            'idusuario' => $securityUser->idusuario,
            'idempresa' => $montreal->idempresa,
            'activo' => 0,
        ]);
        $this->assertDatabaseHas('pbi_security_user_companies', [
            'idusuario' => $securityUser->idusuario,
            'idempresa' => $atlantico->idempresa,
            'activo' => 1,
        ]);
    }

    public function test_user_requires_at_least_one_active_company(): void
    {
        $profile = $this->createProfile();

        $response = $this->asPbiAdministrator()
            ->from(route('security-users.create'))
            ->post(route('security-users.store'), [
                'correo' => 'persona@genz.com.py',
                'idperfil' => $profile->idperfil,
                'activo' => true,
            ]);

        $response->assertRedirect(route('security-users.create'));
        $response->assertSessionHasErrors('company_ids');
    }

    private function createProfile(): SecurityProfile
    {
        return SecurityProfile::query()->create([
            'codigo' => 'COMPRAS',
            'nombre' => 'Compras',
            'powerbi_role_name' => 'RLS_Compras',
            'activo' => true,
        ]);
    }

    private function createCompany(string $name, string $key): SecurityCompany
    {
        return SecurityCompany::query()->create([
            'nombre' => $name,
            'clave_pbi' => $key,
            'activo' => true,
        ]);
    }
}
