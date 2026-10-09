<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use App\Models\MenuItem;
use App\Services\MenuAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function index(): View
    {
        $menuItems = MenuItem::query()
            ->general()
            ->orderBy('orden')
            ->orderBy('idmenu')
            ->paginate(15);

        return view('menu-items.index', compact('menuItems'));
    }

    public function create(): View
    {
        return view('menu-items.create');
    }

    public function store(StoreMenuItemRequest $request, MenuAuditLogger $auditLogger): RedirectResponse
    {
        $validated = $request->validated();
        $connection = (new MenuItem)->getConnectionName();

        DB::connection($connection)->transaction(function () use ($auditLogger, $request, $validated): void {
            $timestamp = now();
            $menuItem = MenuItem::query()->create([
                ...$validated,
                'modulo' => 'GENERAL',
                'requiere_permiso' => true,
                'es_predeterminado' => false,
                'fecha_creacion' => $timestamp,
                'usuario_creacion' => $this->adminUsername($request),
                'fecha_actualizacion' => $timestamp,
                'usuario_actualizacion' => $this->adminUsername($request),
                'fecha_eliminacion' => $validated['activo'] ? null : $timestamp,
                'usuario_eliminacion' => $validated['activo'] ? null : $this->adminUsername($request),
            ]);

            $auditLogger->record('created', $menuItem, $request);
        });

        return redirect()->route('menu-items.index')->with('status', 'La opción de menú fue creada.');
    }

    public function edit(MenuItem $menuItem): View
    {
        $this->ensureEditable($menuItem);

        return view('menu-items.edit', compact('menuItem'));
    }

    public function update(
        UpdateMenuItemRequest $request,
        MenuItem $menuItem,
        MenuAuditLogger $auditLogger,
    ): RedirectResponse {
        $this->ensureEditable($menuItem);
        $validated = $request->validated();
        $before = $menuItem->getAttributes();
        $connection = $menuItem->getConnectionName();

        DB::connection($connection)->transaction(function () use ($auditLogger, $before, $menuItem, $request, $validated): void {
            $timestamp = now();
            $menuItem->fill([
                ...$validated,
                'fecha_actualizacion' => $timestamp,
                'usuario_actualizacion' => $this->adminUsername($request),
                'fecha_eliminacion' => $validated['activo'] ? null : $timestamp,
                'usuario_eliminacion' => $validated['activo'] ? null : $this->adminUsername($request),
            ]);
            $menuItem->save();

            $auditLogger->record('updated', $menuItem, $request, $before);
        });

        return redirect()->route('menu-items.index')->with('status', 'La opción de menú fue actualizada.');
    }

    public function destroy(
        Request $request,
        MenuItem $menuItem,
        MenuAuditLogger $auditLogger,
    ): RedirectResponse {
        $this->ensureEditable($menuItem);
        $before = $menuItem->getAttributes();
        $connection = $menuItem->getConnectionName();

        DB::connection($connection)->transaction(function () use ($auditLogger, $before, $menuItem, $request): void {
            $timestamp = now();
            $menuItem->update([
                'activo' => false,
                'fecha_actualizacion' => $timestamp,
                'usuario_actualizacion' => $this->adminUsername($request),
                'fecha_eliminacion' => $timestamp,
                'usuario_eliminacion' => $this->adminUsername($request),
            ]);

            $auditLogger->record('disabled', $menuItem, $request, $before);
        });

        return redirect()->route('menu-items.index')->with('status', 'La opción de menú fue deshabilitada.');
    }

    private function ensureEditable(MenuItem $menuItem): void
    {
        abort_if($menuItem->isDefault(), 403, 'La opción predeterminada no se puede modificar.');
    }

    private function adminUsername(Request $request): string
    {
        return (string) $request->session()->get('pbi_admin_username');
    }
}
