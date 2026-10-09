<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSecurityUserRequest;
use App\Http\Requests\UpdateSecurityUserRequest;
use App\Models\SecurityCompany;
use App\Models\SecurityProfile;
use App\Models\SecurityUser;
use App\Models\SecurityUserCompany;
use App\Services\MenuAuditLogger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SecurityUserController extends Controller
{
    public function index(): View
    {
        $securityUsers = SecurityUser::query()
            ->with([
                'profile:idperfil,codigo,nombre',
                'companyAssignments' => fn ($query) => $query->active()->with('company:idempresa,nombre'),
            ])
            ->orderBy('correo')
            ->orderBy('idusuario')
            ->paginate(20);

        return view('security-users.index', compact('securityUsers'));
    }

    public function create(): View
    {
        return view('security-users.create', [
            'securityProfiles' => $this->availableProfiles(),
            'securityCompanies' => $this->availableCompanies(),
            'selectedCompanyIds' => [],
        ]);
    }

    public function store(
        StoreSecurityUserRequest $request,
        MenuAuditLogger $auditLogger,
    ): RedirectResponse {
        $validated = $request->validated();
        $connection = (new SecurityUser)->getConnectionName();

        DB::connection($connection)->transaction(function () use ($auditLogger, $request, $validated): void {
            $timestamp = now();
            $securityUser = SecurityUser::query()->create([
                'nombre' => $validated['nombre'] ?: null,
                'correo' => $validated['correo'],
                'idperfil' => $validated['idperfil'],
                'activo' => $validated['activo'],
                'fecha_creacion' => $timestamp,
                'usuario_creacion' => $this->adminUsername($request),
                'fecha_actualizacion' => $timestamp,
                'usuario_actualizacion' => $this->adminUsername($request),
                'fecha_eliminacion' => $validated['activo'] ? null : $timestamp,
                'usuario_eliminacion' => $validated['activo'] ? null : $this->adminUsername($request),
            ]);

            $auditLogger->record('created', $securityUser, $request);
            $this->syncCompanyAssignments($securityUser, $validated['company_ids'], $request, $auditLogger);
        });

        return redirect()->route('security-users.index')->with('status', 'El usuario y sus empresas fueron asignados.');
    }

    public function edit(SecurityUser $securityUser): View
    {
        return view('security-users.edit', [
            'securityUser' => $securityUser,
            'securityProfiles' => $this->availableProfiles(),
            'securityCompanies' => $this->availableCompanies(),
            'selectedCompanyIds' => $securityUser->companyAssignments()
                ->active()
                ->pluck('idempresa')
                ->all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateSecurityUserRequest $request,
        SecurityUser $securityUser,
        MenuAuditLogger $auditLogger,
    ): RedirectResponse {
        $validated = $request->validated();
        $before = $securityUser->getAttributes();
        $connection = $securityUser->getConnectionName();

        DB::connection($connection)->transaction(function () use ($auditLogger, $before, $request, $securityUser, $validated): void {
            $timestamp = now();
            $securityUser->update([
                'nombre' => $validated['nombre'] ?: null,
                'correo' => $validated['correo'],
                'idperfil' => $validated['idperfil'],
                'activo' => $validated['activo'],
                'fecha_actualizacion' => $timestamp,
                'usuario_actualizacion' => $this->adminUsername($request),
                'fecha_eliminacion' => $validated['activo'] ? null : $timestamp,
                'usuario_eliminacion' => $validated['activo'] ? null : $this->adminUsername($request),
            ]);

            $auditLogger->record('updated', $securityUser, $request, $before);
            $this->syncCompanyAssignments($securityUser, $validated['company_ids'], $request, $auditLogger);
        });

        return redirect()->route('security-users.index')->with('status', 'El usuario fue actualizado.');
    }

    public function destroy(
        Request $request,
        SecurityUser $securityUser,
        MenuAuditLogger $auditLogger,
    ): RedirectResponse {
        $before = $securityUser->getAttributes();
        $connection = $securityUser->getConnectionName();

        DB::connection($connection)->transaction(function () use ($auditLogger, $before, $request, $securityUser): void {
            $timestamp = now();
            $securityUser->update([
                'activo' => false,
                'fecha_actualizacion' => $timestamp,
                'usuario_actualizacion' => $this->adminUsername($request),
                'fecha_eliminacion' => $timestamp,
                'usuario_eliminacion' => $this->adminUsername($request),
            ]);

            $auditLogger->record('disabled', $securityUser, $request, $before);
        });

        return redirect()->route('security-users.index')->with('status', 'El usuario fue desactivado.');
    }

    /**
     * @return Collection<int, SecurityProfile>
     */
    private function availableProfiles(): Collection
    {
        return SecurityProfile::query()
            ->active()
            ->orderBy('nombre')
            ->get(['idperfil', 'codigo', 'nombre']);
    }

    /**
     * @return Collection<int, SecurityCompany>
     */
    private function availableCompanies(): Collection
    {
        return SecurityCompany::query()
            ->active()
            ->orderBy('nombre')
            ->get(['idempresa', 'nombre', 'clave_pbi']);
    }

    /**
     * @param  array<int, mixed>  $companyIds
     */
    private function syncCompanyAssignments(
        SecurityUser $securityUser,
        array $companyIds,
        Request $request,
        MenuAuditLogger $auditLogger,
    ): void {
        $selectedCompanyIds = collect($companyIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();
        $assignments = $securityUser->companyAssignments()->get()->keyBy('idempresa');
        $timestamp = now();

        foreach ($assignments->where('activo', true) as $assignment) {
            if ($selectedCompanyIds->contains((int) $assignment->idempresa)) {
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

        foreach ($selectedCompanyIds as $companyId) {
            $assignment = $assignments->get($companyId);

            if ($assignment === null) {
                $assignment = SecurityUserCompany::query()->create([
                    'idusuario' => $securityUser->idusuario,
                    'idempresa' => $companyId,
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
