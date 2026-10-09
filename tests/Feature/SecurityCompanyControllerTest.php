<?php

namespace Tests\Feature;

use App\Models\SecurityCompany;
use App\Models\SecurityProfile;
use App\Models\SecurityUser;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SecurityCompanyControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_administrator_creates_a_company_with_its_power_bi_key(): void
    {
        $response = $this->asPbiAdministrator()->post(route('security-companies.store'), [
            'nombre' => 'Montreal',
            'clave_pbi' => 'MONTREAL',
            'activo' => true,
        ]);

        $response->assertRedirect(route('security-companies.index'));
        $this->assertDatabaseHas('pbi_security_companies', [
            'nombre' => 'Montreal',
            'clave_pbi' => 'MONTREAL',
            'activo' => 1,
        ]);
        $this->assertDatabaseHas('pbi_access_audit_logs', [
            'entidad' => 'pbi_security_companies',
            'accion' => 'created',
            'usuario_admin' => 'admin',
        ]);
    }

    public function test_company_key_must_be_unique(): void
    {
        SecurityCompany::query()->create([
            'nombre' => 'Montreal',
            'clave_pbi' => 'MONTREAL',
            'activo' => true,
        ]);

        $response = $this->asPbiAdministrator()
            ->from(route('security-companies.create'))
            ->post(route('security-companies.store'), [
                'nombre' => 'Montreal alternativo',
                'clave_pbi' => 'MONTREAL',
                'activo' => true,
            ]);

        $response->assertRedirect(route('security-companies.create'));
        $response->assertSessionHasErrors('clave_pbi');
    }

    public function test_company_with_active_assignments_cannot_be_deactivated(): void
    {
        $profile = SecurityProfile::query()->create([
            'codigo' => 'COMPLETO',
            'nombre' => 'Completo',
            'powerbi_role_name' => 'RLS_Completo',
            'activo' => true,
        ]);
        $company = SecurityCompany::query()->create([
            'nombre' => 'Montreal',
            'clave_pbi' => 'MONTREAL',
            'activo' => true,
        ]);
        $user = SecurityUser::query()->create([
            'correo' => 'persona@genz.com.py',
            'idperfil' => $profile->idperfil,
            'activo' => true,
        ]);
        $user->companyAssignments()->create([
            'idempresa' => $company->idempresa,
            'activo' => true,
        ]);

        $response = $this->asPbiAdministrator()
            ->from(route('security-companies.edit', $company))
            ->put(route('security-companies.update', $company), [
                'nombre' => 'Montreal',
                'clave_pbi' => 'MONTREAL',
                'activo' => false,
            ]);

        $response->assertRedirect(route('security-companies.edit', $company));
        $response->assertSessionHasErrors('activo');
        $this->assertTrue($company->fresh()->activo);
    }
}
