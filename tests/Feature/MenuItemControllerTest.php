<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MenuItemControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_administrator_creates_a_general_menu_item_and_audit_record(): void
    {
        $response = $this->asPbiAdministrator()->post(route('menu-items.store'), [
            'nombre' => 'RESUMEN',
            'pagina_destino' => 'Control Resumido',
            'orden' => 3,
            'activo' => true,
        ]);

        $response->assertRedirect(route('menu-items.index'));
        $this->assertDatabaseHas('pbi_menu_items', [
            'nombre' => 'RESUMEN',
            'pagina_destino' => 'Control Resumido',
            'modulo' => 'GENERAL',
            'activo' => 1,
            'requiere_permiso' => 1,
        ]);
        $this->assertDatabaseHas('pbi_access_audit_logs', [
            'entidad' => 'pbi_menu_items',
            'accion' => 'created',
            'usuario_admin' => 'admin',
        ]);
    }

    public function test_menu_item_creation_requires_the_display_name_destination_and_order(): void
    {
        $response = $this->asPbiAdministrator()->from(route('menu-items.create'))->post(route('menu-items.store'), []);

        $response->assertRedirect(route('menu-items.create'));
        $response->assertSessionHasErrors(['nombre', 'pagina_destino', 'orden']);
    }

    public function test_default_menu_item_cannot_be_modified(): void
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

        $response = $this->asPbiAdministrator()->put(route('menu-items.update', $menuItem), [
            'nombre' => 'MODIFICADO',
            'pagina_destino' => 'Otro',
            'orden' => 0,
            'activo' => true,
        ]);

        $response->assertForbidden();
        $this->assertSame('SELECCIONAR', $menuItem->fresh()->nombre);
    }
}
