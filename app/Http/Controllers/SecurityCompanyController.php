<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSecurityCompanyRequest;
use App\Http\Requests\UpdateSecurityCompanyRequest;
use App\Models\SecurityCompany;
use App\Services\MenuAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SecurityCompanyController extends Controller
{
    public function index(): View
    {
        $securityCompanies = SecurityCompany::query()
            ->withCount([
                'userAssignments as active_users_count' => fn ($query) => $query->active(),
            ])
            ->orderBy('nombre')
            ->orderBy('idempresa')
            ->paginate(20);

        return view('security-companies.index', compact('securityCompanies'));
    }

    public function create(): View
    {
        return view('security-companies.create');
    }

    public function store(
        StoreSecurityCompanyRequest $request,
        MenuAuditLogger $auditLogger,
    ): RedirectResponse {
        $validated = $request->validated();
        $connection = (new SecurityCompany)->getConnectionName();

        DB::connection($connection)->transaction(function () use ($auditLogger, $request, $validated): void {
            $timestamp = now();
            $company = SecurityCompany::query()->create([
                ...$validated,
                'fecha_creacion' => $timestamp,
                'usuario_creacion' => $this->adminUsername($request),
                'fecha_actualizacion' => $timestamp,
                'usuario_actualizacion' => $this->adminUsername($request),
                'fecha_eliminacion' => $validated['activo'] ? null : $timestamp,
                'usuario_eliminacion' => $validated['activo'] ? null : $this->adminUsername($request),
            ]);

            $auditLogger->record('created', $company, $request);
        });

        return redirect()->route('security-companies.index')->with('status', 'La empresa fue creada.');
    }

    public function edit(SecurityCompany $securityCompany): View
    {
        return view('security-companies.edit', compact('securityCompany'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateSecurityCompanyRequest $request,
        SecurityCompany $securityCompany,
        MenuAuditLogger $auditLogger,
    ): RedirectResponse {
        $validated = $request->validated();

        if (! $validated['activo'] && $securityCompany->userAssignments()->active()->exists()) {
            return back()
                ->withInput()
                ->withErrors(['activo' => 'Retire primero las asignaciones activas de esta empresa.']);
        }

        $before = $securityCompany->getAttributes();
        $connection = $securityCompany->getConnectionName();

        DB::connection($connection)->transaction(function () use ($auditLogger, $before, $request, $securityCompany, $validated): void {
            $timestamp = now();
            $securityCompany->update([
                ...$validated,
                'fecha_actualizacion' => $timestamp,
                'usuario_actualizacion' => $this->adminUsername($request),
                'fecha_eliminacion' => $validated['activo'] ? null : $timestamp,
                'usuario_eliminacion' => $validated['activo'] ? null : $this->adminUsername($request),
            ]);

            $auditLogger->record('updated', $securityCompany, $request, $before);
        });

        return redirect()->route('security-companies.index')->with('status', 'La empresa fue actualizada.');
    }

    public function destroy(
        Request $request,
        SecurityCompany $securityCompany,
        MenuAuditLogger $auditLogger,
    ): RedirectResponse {
        if ($securityCompany->userAssignments()->active()->exists()) {
            return redirect()
                ->route('security-companies.index')
                ->with('error', 'Retire primero las asignaciones activas de esta empresa.');
        }

        $before = $securityCompany->getAttributes();
        $connection = $securityCompany->getConnectionName();

        DB::connection($connection)->transaction(function () use ($auditLogger, $before, $request, $securityCompany): void {
            $timestamp = now();
            $securityCompany->update([
                'activo' => false,
                'fecha_actualizacion' => $timestamp,
                'usuario_actualizacion' => $this->adminUsername($request),
                'fecha_eliminacion' => $timestamp,
                'usuario_eliminacion' => $this->adminUsername($request),
            ]);

            $auditLogger->record('disabled', $securityCompany, $request, $before);
        });

        return redirect()->route('security-companies.index')->with('status', 'La empresa fue desactivada.');
    }

    private function adminUsername(Request $request): string
    {
        return (string) $request->session()->get('pbi_admin_username');
    }
}
