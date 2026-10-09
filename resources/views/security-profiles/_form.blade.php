@php
    $formMenuItemIds = old('menu_item_ids', $selectedMenuItemIds);
    $formMenuItemIds = is_array($formMenuItemIds) ? array_map('intval', $formMenuItemIds) : [];
@endphp

<div class="grid gap-5">
    @isset ($securityProfile)
        <div class="grid gap-2">
            <span class="text-sm font-medium text-slate-700">Código técnico</span>
            <code class="rounded-md bg-slate-100 px-3 py-2 text-sm text-slate-800">{{ $securityProfile->codigo }}</code>
        </div>
    @else
        <div class="grid gap-2">
            <label for="codigo" class="text-sm font-medium text-slate-700">Código técnico</label>
            <input id="codigo" name="codigo" type="text" value="{{ old('codigo') }}" required maxlength="50" pattern="[A-Z][A-Z0-9_]*" class="rounded-md border-slate-300 px-3 py-2 font-mono shadow-sm outline-none ring-sky-600 transition focus:ring-2">
            <p class="text-xs text-slate-500">Use mayúsculas, números y guiones bajos; por ejemplo: <code>COMPRAS</code>.</p>
        </div>
    @endisset

    <div class="grid gap-2">
        <label for="nombre" class="text-sm font-medium text-slate-700">Nombre visible</label>
        <input id="nombre" name="nombre" type="text" value="{{ old('nombre', $securityProfile->nombre ?? '') }}" required maxlength="100" class="rounded-md border-slate-300 px-3 py-2 shadow-sm outline-none ring-sky-600 transition focus:ring-2">
    </div>

    <div class="grid gap-2">
        <label for="powerbi_role_name" class="text-sm font-medium text-slate-700">Nombre exacto del rol en Power BI Service</label>
        <input id="powerbi_role_name" name="powerbi_role_name" type="text" value="{{ old('powerbi_role_name', $securityProfile->powerbi_role_name ?? '') }}" required maxlength="100" class="rounded-md border-slate-300 px-3 py-2 font-mono shadow-sm outline-none ring-sky-600 transition focus:ring-2">
    </div>

    <fieldset class="grid gap-3">
        <legend class="text-sm font-medium text-slate-700">Opciones de menú del perfil</legend>
        <p class="text-xs text-slate-500">Las excepciones individuales pueden añadirse después desde la sección Excepciones.</p>
        <div class="grid gap-2 sm:grid-cols-2">
            @foreach ($menuItems as $menuItem)
                <label class="flex items-center gap-3 rounded-md border border-slate-200 px-3 py-3 text-sm">
                    <input name="menu_item_ids[]" type="checkbox" value="{{ $menuItem->idmenu }}" @checked(in_array((int) $menuItem->idmenu, $formMenuItemIds, true)) class="size-4 rounded border-slate-300 text-sky-700 focus:ring-sky-600">
                    <span class="font-medium text-slate-800">{{ $menuItem->nombre }}</span>
                </label>
            @endforeach
        </div>
    </fieldset>

    <label class="flex items-center gap-3 rounded-md border border-slate-200 px-3 py-3 text-sm">
        <input type="hidden" name="activo" value="0">
        <input name="activo" type="checkbox" value="1" @checked(old('activo', $securityProfile->activo ?? true)) class="size-4 rounded border-slate-300 text-sky-700 focus:ring-sky-600">
        <span>
            <span class="block font-medium text-slate-800">Perfil activo</span>
            <span class="block text-slate-500">No se puede desactivar mientras tenga usuarios activos.</span>
        </span>
    </label>

    <div class="flex flex-wrap items-center gap-3 pt-2">
        <button type="submit" class="rounded-md bg-sky-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-800">Guardar</button>
        <a href="{{ route('security-profiles.index') }}" class="rounded-md px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancelar</a>
    </div>
</div>
