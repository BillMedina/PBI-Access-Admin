<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSecurityProfileRequest;
use App\Http\Requests\UpdateSecurityProfileRequest;
use App\Models\MenuItem;
use App\Models\ProfileMenuItem;
use App\Models\SecurityProfile;
use App\Services\MenuAuditLogger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SecurityProfileController extends Controller
{
    public function index(): View
    {
        $securityProfiles = SecurityProfile::query()
            ->withCount([
                'securityUsers as active_users_count' => fn ($query) => $query->active(),
            ])
            ->orderBy('nombre')
            ->orderBy('idperfil')
            ->paginate(20);

        return view('security-profiles.index', compact('securityProfiles'));
    }

    public function create(): View
    {
        return view('security-profiles.create', [
            'menuItems' => $this->availableMenuItems(),
            'selectedMenuItemIds' => [],
        ]);
    }

    public function store(
        StoreSecurityProfileRequest $request,
        MenuAuditLogger $auditLogger,
    ): RedirectResponse {
        $validated = $request->validated();
        $connection = (new SecurityProfile)->getConnectionName();

        DB::connection($connection)->transaction(function () use ($auditLogger, $request, $validated): void {
            $timestamp = now();
            $profile = SecurityProfile::query()->create([
                'codigo' => $validated['codigo'],
                'nombre' => $validated['nombre'],
                'powerbi_role_name' => $validated['powerbi_role_name'],
                'activo' => $validated['activo'],
                'fecha_creacion' => $timestamp,
                'usuario_creacion' => $this->adminUsername($request),
                'fecha_actualizacion' => $timestamp,
                'usuario_actualizacion' => $this->adminUsername($request),
                'fecha_eliminacion' => $validated['activo'] ? null : $timestamp,
                'usuario_eliminacion' => $validated['activo'] ? null : $this->adminUsername($request),
            ]);

            $auditLogger->record('created', $profile, $request);
            $this->syncMenuAssignments($profile, $validated['menu_item_ids'] ?? [], $request, $auditLogger);
        });

        return redirect()->route('security-profiles.index')->with('status', 'El perfil fue creado.');
    }

    public function edit(SecurityProfile $securityProfile): View
    {
        return view('security-profiles.edit', [
            'securityProfile' => $securityProfile,
            'menuItems' => $this->availableMenuItems(),
            'selectedMenuItemIds' => $securityProfile->menuAssignments()
                ->active()
                ->pluck('idmenu')
                ->all(),
        ]);
    }

    public function update(
        UpdateSecurityProfileRequest $request,
        SecurityProfile $securityProfile,
        MenuAuditLogger $auditLogger,
    ): RedirectResponse {
        $validated = $request->validated();

        if (! $validated['activo'] && $securityProfile->securityUsers()->active()->exists()) {
            return back()
                ->withInput()
                ->withErrors(['activo' => 'No se puede desactivar un perfil con usuarios activos.']);
        }

        $before = $securityProfile->getAttributes();
        $connection = $securityProfile->getConnectionName();

        DB::connection($connection)->transaction(function () use ($auditLogger, $before, $request, $securityProfile, $validated): void {
            $timestamp = now();
            $securityProfile->update([
                'nombre' => $validated['nombre'],
                'powerbi_role_name' => $validated['powerbi_role_name'],
                'activo' => $validated['activo'],
                'fecha_actualizacion' => $timestamp,
                'usuario_actualizacion' => $this->adminUsername($request),
                'fecha_eliminacion' => $validated['activo'] ? null : $timestamp,
                'usuario_eliminacion' => $validated['activo'] ? null : $this->adminUsername($request),
            ]);

            $auditLogger->record('updated', $securityProfile, $request, $before);
            $this->syncMenuAssignments($securityProfile, $validated['menu_item_ids'] ?? [], $request, $auditLogger);
        });

        return redirect()->route('security-profiles.index')->with('status', 'El perfil fue actualizado.');
    }

    public function destroy(
        Request $request,
        SecurityProfile $securityProfile,
        MenuAuditLogger $auditLogger,
    ): RedirectResponse {
        if ($securityProfile->securityUsers()->active()->exists()) {
            return redirect()
                ->route('security-profiles.index')
                ->with('error', 'No se puede desactivar un perfil con usuarios activos.');
        }

        $before = $securityProfile->getAttributes();
        $connection = $securityProfile->getConnectionName();

        DB::connection($connection)->transaction(function () use ($auditLogger, $before, $request, $securityProfile): void {
            $timestamp = now();
            $securityProfile->update([
                'activo' => false,
                'fecha_actualizacion' => $timestamp,
                'usuario_actualizacion' => $this->adminUsername($request),
                'fecha_eliminacion' => $timestamp,
                'usuario_eliminacion' => $this->adminUsername($request),
            ]);

            $auditLogger->record('disabled', $securityProfile, $request, $before);
        });

        return redirect()->route('security-profiles.index')->with('status', 'El perfil fue desactivado.');
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

    /**
     * @param  array<int, mixed>  $menuItemIds
     */
    private function syncMenuAssignments(
        SecurityProfile $profile,
        array $menuItemIds,
        Request $request,
        MenuAuditLogger $auditLogger,
    ): void {
        $selectedMenuItemIds = collect($menuItemIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();
        $assignments = $profile->menuAssignments()->get()->keyBy('idmenu');
        $timestamp = now();

        foreach ($assignments->where('activo', true) as $assignment) {
            if ($selectedMenuItemIds->contains((int) $assignment->idmenu)) {
                continue;
            }

            $before = $assignment->getAttributes();
            $assignment->update([
                'activo' => false,
                'fecha_actualizacion' => $timestamp,
                'usuario_actualizacion' => $this->adminUsername($request),
                'fecha_eliminacion' => $timestamp,
                'usuario_eliminacion' => $this->adminUsername($request),
            ]);
            $auditLogger->record('revoked', $assignment, $request, $before);
        }

        foreach ($selectedMenuItemIds as $menuItemId) {
            $assignment = $assignments->get($menuItemId);

            if ($assignment === null) {
                $assignment = ProfileMenuItem::query()->create([
                    'idperfil' => $profile->idperfil,
                    'idmenu' => $menuItemId,
                    'activo' => true,
                    'fecha_creacion' => $timestamp,
                    'usuario_creacion' => $this->adminUsername($request),
                    'fecha_actualizacion' => $timestamp,
                    'usuario_actualizacion' => $this->adminUsername($request),
                ]);
                $auditLogger->record('created', $assignment, $request);

                continue;
            }

            if ($assignment->activo) {
                continue;
            }

            $before = $assignment->getAttributes();
            $assignment->update([
                'activo' => true,
                'fecha_actualizacion' => $timestamp,
                'usuario_actualizacion' => $this->adminUsername($request),
                'fecha_eliminacion' => null,
                'usuario_eliminacion' => null,
            ]);
            $auditLogger->record('reactivated', $assignment, $request, $before);
        }
    }

    private function adminUsername(Request $request): string
    {
        return (string) $request->session()->get('pbi_admin_username');
    }
}
