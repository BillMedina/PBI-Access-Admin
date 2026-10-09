<div class="grid gap-5">
    <div class="grid gap-2">
        <label for="nombre" class="text-sm font-medium text-slate-700">Nombre visible</label>
        <input id="nombre" name="nombre" type="text" value="{{ old('nombre', $securityCompany->nombre ?? '') }}" required maxlength="150" class="rounded-md border-slate-300 px-3 py-2 shadow-sm outline-none ring-sky-600 transition focus:ring-2">
    </div>

    <div class="grid gap-2">
        <label for="clave_pbi" class="text-sm font-medium text-slate-700">Clave de empresa en Power BI</label>
        <input id="clave_pbi" name="clave_pbi" type="text" value="{{ old('clave_pbi', $securityCompany->clave_pbi ?? '') }}" required maxlength="255" class="rounded-md border-slate-300 px-3 py-2 font-mono shadow-sm outline-none ring-sky-600 transition focus:ring-2">
        <p class="text-xs text-slate-500">Use la clave estable del modelo; no un texto traducido o decorativo.</p>
    </div>

    <label class="flex items-center gap-3 rounded-md border border-slate-200 px-3 py-3 text-sm">
        <input type="hidden" name="activo" value="0">
        <input name="activo" type="checkbox" value="1" @checked(old('activo', $securityCompany->activo ?? true)) class="size-4 rounded border-slate-300 text-sky-700 focus:ring-sky-600">
        <span>
            <span class="block font-medium text-slate-800">Empresa activa</span>
            <span class="block text-slate-500">Primero retire las asignaciones activas antes de desactivarla.</span>
        </span>
    </label>

    <div class="flex flex-wrap items-center gap-3 pt-2">
        <button type="submit" class="rounded-md bg-sky-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-800">Guardar</button>
        <a href="{{ route('security-companies.index') }}" class="rounded-md px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancelar</a>
    </div>
</div>
