<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\SecurityProfile;
use App\Models\SecurityUser;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SecurityProfileControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_administrator_creates_a_profile_with_menu_assignments(): void
    {
        $menuItem = $this->createMenuItem();

        $response = $this->asPbiAdministrator()->post(route('security-profiles.store'), [
            'codigo' => 'COMPRAS',
            'nombre' => 'Compras',
            'powerbi_role_name' => 'RLS_Compras',
            'menu_item_ids' => [$menuItem->idmenu],
            'activo' => true,
        ]);

        $response->assertRedirect(route('security-profiles.index'));
        $this->assertDatabaseHas('pbi_security_profiles', [
            'codigo' => 'COMPRAS',
            'nombre' => 'Compras',
            'powerbi_role_name' => 'RLS_Compras',
            'activo' => 1,
        ]);
        $this->assertDatabaseHas('pbi_security_profile_menu_items', [
            'idmenu' => $menuItem->idmenu,
            'activo' => 1,
        ]);
        $this->assertDatabaseHas('pbi_access_audit_logs', [
            'entidad' => 'pbi_security_profiles',
            'accion' => 'created',
            'usuario_admin' => 'admin',
        ]);
    }

    public function test_profile_code_is_required_to_use_the_supported_technical_format(): void
    {
        $response = $this->asPbiAdministrator()
            ->from(route('security-profiles.create'))
            ->post(route('security-profiles.store'), [
                'codigo' => 'compras libres',
                'nombre' => 'Compras',
                'powerbi_role_name' => 'RLS_Compras',
                'activo' => true,
            ]);

        $response->assertRedirect(route('security-profiles.create'));
        $response->assertSessionHasErrors('codigo');
    }

    public function test_profile_with_active_users_cannot_be_deactivated(): void
    {
        $profile = $this->createProfile();
        SecurityUser::query()->create([
            'correo' => 'persona@genz.com.py',
            'idperfil' => $profile->idperfil,
            'activo' => true,
        ]);

        $response = $this->asPbiAdministrator()
            ->from(route('security-profiles.edit', $profile))
            ->put(route('security-profiles.update', $profile), [
                'nombre' => $profile->nombre,
                'powerbi_role_name' => $profile->powerbi_role_name,
                'menu_item_ids' => [],
                'activo' => false,
            ]);

        $response->assertRedirect(route('security-profiles.edit', $profile));
        $response->assertSessionHasErrors('activo');
        $this->assertTrue($profile->fresh()->activo);
    }

    private function createMenuItem(): MenuItem
    {
        return MenuItem::query()->create([
            'nombre' => 'GENERAL',
            'pagina_destino' => 'Principal',
            'modulo' => 'GENERAL',
            'orden' => 1,
            'activo' => true,
            'requiere_permiso' => true,
            'es_predeterminado' => false,
        ]);
    }

    private function createProfile(): SecurityProfile
    {
        return SecurityProfile::query()->create([
            'codigo' => 'COMPLETO',
            'nombre' => 'Completo',
            'powerbi_role_name' => 'RLS_Completo',
            'activo' => true,
        ]);
    }
}
