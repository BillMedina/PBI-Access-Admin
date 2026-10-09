<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMenuUserPermissionRequest;
use App\Http\Requests\UpdateMenuUserPermissionRequest;
use App\Models\MenuItem;
use App\Models\MenuUserPermission;
use App\Services\MenuAuditLogger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MenuUserPermissionController extends Controller
{
    public function index(): View
    {
        $permissions = MenuUserPermission::query()
            ->with('menuItem:idmenu,nombre')
            ->whereHas('menuItem', fn ($query) => $query->where('modulo', 'GENERAL'))
            ->orderBy('correo')
            ->orderBy('idmenu_permiso')
            ->paginate(20);

        return view('menu-permissions.index', compact('permissions'));
    }

    public function create(): View
    {
        return view('menu-permissions.create', [
            'menuItems' => $this->availableMenuItems(),
        ]);
    }

    public function store(
        StoreMenuUserPermissionRequest $request,
        MenuAuditLogger $auditLogger,
    ): RedirectResponse {
        $validated = $request->validated();
        $connection = (new MenuUserPermission)->getConnectionName();

        DB::connection($connection)->transaction(function () use ($auditLogger, $request, $validated): void {
            $timestamp = now();
            $permission = MenuUserPermission::query()
                ->where('correo', $validated['correo'])
                ->where('idmenu', $validated['idmenu'])
                ->first();

            if ($permission === null) {
                $permission = MenuUserPermission::query()->create([
                    'correo' => $validated['correo'],
                    'idmenu' => $validated['idmenu'],
                    'activo' => true,
                    'fecha_creacion' => $timestamp,
                    'usuario_creacion' => $this->adminUsername($request),
                    'fecha_actualizacion' => $timestamp,
                    'usuario_actualizacion' => $this->adminUsername($request),
                ]);

                $auditLogger->record('created', $permission, $request);

                return;
            }

            $before = $permission->getAttributes();
            $permission->update([
                'activo' => true,
                'fecha_actualizacion' => $timestamp,
                'usuario_actualizacion' => $this->adminUsername($request),
                'fecha_eliminacion' => null,
                'usuario_eliminacion' => null,
            ]);

            $auditLogger->record('reactivated', $permission, $request, $before);
        });

        return redirect()->route('menu-permissions.index')->with('status', 'El permiso fue asignado.');
    }

    public function edit(MenuUserPermission $menuPermission): View
    {
        return view('menu-permissions.edit', [
            'menuPermission' => $menuPermission,
            'menuItems' => $this->availableMenuItems(),
        ]);
    }

    public function update(
        UpdateMenuUserPermissionRequest $request,
        MenuUserPermission $menuPermission,
        MenuAuditLogger $auditLogger,
    ): RedirectResponse {
        $validated = $request->validated();
        $before = $menuPermission->getAttributes();
        $connection = $menuPermission->getConnectionName();

        DB::connection($connection)->transaction(function () use ($auditLogger, $menuPermission, $request, $validated, $before): void {
            $timestamp = now();
            $menuPermission->update([
                ...$validated,
                'fecha_actualizacion' => $timestamp,
                'usuario_actualizacion' => $this->adminUsername($request),
                'fecha_eliminacion' => $validated['activo'] ? null : $timestamp,
                'usuario_eliminacion' => $validated['activo'] ? null : $this->adminUsername($request),
            ]);

            $auditLogger->record('updated', $menuPermission, $request, $before);
        });

        return redirect()->route('menu-permissions.index')->with('status', 'El permiso fue actualizado.');
    }

    public function destroy(
        Request $request,
        MenuUserPermission $menuPermission,
        MenuAuditLogger $auditLogger,
    ): RedirectResponse {
        $before = $menuPermission->getAttributes();
        $connection = $menuPermission->getConnectionName();

        DB::connection($connection)->transaction(function () use ($auditLogger, $menuPermission, $request, $before): void {
            $timestamp = now();
            $menuPermission->update([
                'activo' => false,
                'fecha_actualizacion' => $timestamp,
                'usuario_actualizacion' => $this->adminUsername($request),
                'fecha_eliminacion' => $timestamp,
                'usuario_eliminacion' => $this->adminUsername($request),
            ]);

            $auditLogger->record('revoked', $menuPermission, $request, $before);
        });

        return redirect()->route('menu-permissions.index')->with('status', 'El permiso fue revocado.');
    }

    /**
     * @return Collection<int, MenuItem>
     */
    private function availableMenuItems(): Collection
    {
        return MenuItem::query()
            ->general()
            ->where('activo', true)
            ->where('requiere_permiso', true)
            ->orderBy('orden')
            ->orderBy('idmenu')
            ->get(['idmenu', 'nombre']);
    }

    private function adminUsername(Request $request): string
    {
        return (string) $request->session()->get('pbi_admin_username');
    }
}
