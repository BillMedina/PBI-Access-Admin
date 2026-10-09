<div class="grid gap-5">
    <div class="grid gap-2">
        <label for="nombre" class="text-sm font-medium text-slate-700">Nombre visible</label>
        <input id="nombre" name="nombre" type="text" value="{{ old('nombre', $menuItem->nombre ?? '') }}" required maxlength="100" class="rounded-md border-slate-300 px-3 py-2 shadow-sm outline-none ring-sky-600 transition focus:ring-2">
    </div>

    <div class="grid gap-2">
        <label for="pagina_destino" class="text-sm font-medium text-slate-700">Destino Power BI</label>
        <input id="pagina_destino" name="pagina_destino" type="text" value="{{ old('pagina_destino', $menuItem->pagina_destino ?? '') }}" required maxlength="255" class="rounded-md border-slate-300 px-3 py-2 shadow-sm outline-none ring-sky-600 transition focus:ring-2">
        <p class="text-xs text-slate-500">Debe coincidir con la clave de navegación configurada en el reporte.</p>
    </div>

    <div class="grid gap-2">
        <label for="orden" class="text-sm font-medium text-slate-700">Orden</label>
        <input id="orden" name="orden" type="number" min="0" max="9999" value="{{ old('orden', $menuItem->orden ?? 1) }}" required class="w-32 rounded-md border-slate-300 px-3 py-2 shadow-sm outline-none ring-sky-600 transition focus:ring-2">
    </div>

    <label class="flex items-center gap-3 rounded-md border border-slate-200 px-3 py-3 text-sm">
        <input type="hidden" name="activo" value="0">
        <input name="activo" type="checkbox" value="1" @checked(old('activo', $menuItem->activo ?? true)) class="size-4 rounded border-slate-300 text-sky-700 focus:ring-sky-600">
        <span>
            <span class="block font-medium text-slate-800">Opción activa</span>
            <span class="block text-slate-500">Las opciones inactivas no se envían a Power BI.</span>
        </span>
    </label>

    <div class="flex flex-wrap items-center gap-3 pt-2">
        <button type="submit" class="rounded-md bg-sky-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-800">Guardar</button>
        <a href="{{ route('menu-items.index') }}" class="rounded-md px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancelar</a>
    </div>
</div>
