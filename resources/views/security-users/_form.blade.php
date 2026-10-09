@php
    $formCompanyIds = old('company_ids', $selectedCompanyIds);
    $formCompanyIds = is_array($formCompanyIds) ? array_map('intval', $formCompanyIds) : [];
@endphp

<div class="grid gap-5">
    <div class="grid gap-2">
        <label for="nombre" class="text-sm font-medium text-slate-700">Nombre</label>
        <input id="nombre" name="nombre" type="text" value="{{ old('nombre', $securityUser->nombre ?? '') }}" maxlength="150" autocomplete="name" class="rounded-md border-slate-300 px-3 py-2 shadow-sm outline-none ring-sky-600 transition focus:ring-2">
    </div>

    <div class="grid gap-2">
        <label for="correo" class="text-sm font-medium text-slate-700">Correo corporativo</label>
        <input id="correo" name="correo" type="email" value="{{ old('correo', $securityUser->correo ?? '') }}" required maxlength="255" autocomplete="email" class="rounded-md border-slate-300 px-3 py-2 shadow-sm outline-none ring-sky-600 transition focus:ring-2">
    </div>

    <div class="grid gap-2">
        <label for="idperfil" class="text-sm font-medium text-slate-700">Perfil RLS único</label>
        <select id="idperfil" name="idperfil" required class="rounded-md border-slate-300 px-3 py-2 shadow-sm outline-none ring-sky-600 transition focus:ring-2">
            <option value="">Seleccione un perfil</option>
            @foreach ($securityProfiles as $securityProfileOption)
                <option value="{{ $securityProfileOption->idperfil }}" @selected((string) old('idperfil', $securityUser->idperfil ?? '') === (string) $securityProfileOption->idperfil)>{{ $securityProfileOption->nombre }} ({{ $securityProfileOption->codigo }})</option>
            @endforeach
        </select>
    </div>

    <fieldset class="grid gap-3">
        <legend class="text-sm font-medium text-slate-700">Empresas permitidas</legend>
        <p class="text-xs text-slate-500">Seleccione una o varias. La clave debe coincidir con la dimensión de empresa que usa el PBIX.</p>
        <div class="grid gap-2 sm:grid-cols-2">
            @foreach ($securityCompanies as $securityCompany)
                <label class="flex items-start gap-3 rounded-md border border-slate-200 px-3 py-3 text-sm">
                    <input name="company_ids[]" type="checkbox" value="{{ $securityCompany->idempresa }}" @checked(in_array((int) $securityCompany->idempresa, $formCompanyIds, true)) class="mt-0.5 size-4 rounded border-slate-300 text-sky-700 focus:ring-sky-600">
                    <span>
                        <span class="block font-medium text-slate-800">{{ $securityCompany->nombre }}</span>
                        <span class="block font-mono text-xs text-slate-500">{{ $securityCompany->clave_pbi }}</span>
                    </span>
                </label>
            @endforeach
        </div>
    </fieldset>

    <label class="flex items-center gap-3 rounded-md border border-slate-200 px-3 py-3 text-sm">
        <input type="hidden" name="activo" value="0">
        <input name="activo" type="checkbox" value="1" @checked(old('activo', $securityUser->activo ?? true)) class="size-4 rounded border-slate-300 text-sky-700 focus:ring-sky-600">
        <span>
            <span class="block font-medium text-slate-800">Usuario activo</span>
            <span class="block text-slate-500">Un usuario inactivo no aparece en las vistas de seguridad de Power BI.</span>
        </span>
    </label>

    <div class="flex flex-wrap items-center gap-3 pt-2">
        <button type="submit" class="rounded-md bg-sky-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-800">Guardar</button>
        <a href="{{ route('security-users.index') }}" class="rounded-md px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancelar</a>
    </div>
</div>
