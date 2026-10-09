<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\MenuUserPermission;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MenuUserPermissionControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_administrator_assigns_a_menu_permission_to_a_normalized_email(): void
    {
        $menuItem = $this->createAvailableMenuItem();

        $response = $this->asPbiAdministrator()->post(route('menu-permissions.store'), [
            'correo' => 'Persona@GENZ.COM.PY',
            'idmenu' => $menuItem->idmenu,
        ]);

        $response->assertRedirect(route('menu-permissions.index'));
        $this->assertDatabaseHas('pbi_menu_user_permissions', [
            'correo' => 'persona@genz.com.py',
            'idmenu' => $menuItem->idmenu,
            'activo' => 1,
        ]);
        $this->assertDatabaseHas('pbi_access_audit_logs', [
            'entidad' => 'pbi_menu_user_permissions',
            'accion' => 'created',
            'usuario_admin' => 'admin',
        ]);
    }

    public function test_assigning_an_existing_revoked_permission_reactivates_it_without_duplication(): void
    {
        $menuItem = $this->createAvailableMenuItem();
        MenuUserPermission::query()->create([
            'correo' => 'persona@genz.com.py',
            'idmenu' => $menuItem->idmenu,
            'activo' => false,
        ]);

        $response = $this->asPbiAdministrator()->post(route('menu-permissions.store'), [
            'correo' => 'persona@genz.com.py',
            'idmenu' => $menuItem->idmenu,
        ]);

        $response->assertRedirect(route('menu-permissions.index'));
        $this->assertSame(1, MenuUserPermission::query()->count());
        $this->assertDatabaseHas('pbi_menu_user_permissions', [
            'correo' => 'persona@genz.com.py',
            'idmenu' => $menuItem->idmenu,
            'activo' => 1,
        ]);
    }

    public function test_permission_cannot_be_assigned_to_the_default_menu_item(): void
    {
        $menuItem = MenuItem::query()->create([
            'nombre' => 'SELECCIONAR',
            'pagina_destino' => 'Menu',
            'modulo' => 'GENERAL',
            'orden' => 0,
            'activo' => true,
            'requiere_permiso' => false,
            'es_predeterminado' => true,
        ]);

        $response = $this->asPbiAdministrator()
            ->from(route('menu-permissions.create'))
            ->post(route('menu-permissions.store'), [
                'correo' => 'persona@genz.com.py',
                'idmenu' => $menuItem->idmenu,
            ]);

        $response->assertRedirect(route('menu-permissions.create'));
        $response->assertSessionHasErrors('idmenu');
    }

    private function createAvailableMenuItem(): MenuItem
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
}
