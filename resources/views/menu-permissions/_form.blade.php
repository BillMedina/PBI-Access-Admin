<div class="grid gap-5">
    <div class="grid gap-2">
        <label for="correo" class="text-sm font-medium text-slate-700">Correo corporativo</label>
        <input id="correo" name="correo" type="email" value="{{ old('correo', $menuPermission->correo ?? '') }}" required maxlength="255" autocomplete="email" class="rounded-md border-slate-300 px-3 py-2 shadow-sm outline-none ring-sky-600 transition focus:ring-2">
    </div>

    <div class="grid gap-2">
        <label for="idmenu" class="text-sm font-medium text-slate-700">Opción del menú general</label>
        <select id="idmenu" name="idmenu" required class="rounded-md border-slate-300 px-3 py-2 shadow-sm outline-none ring-sky-600 transition focus:ring-2">
            <option value="">Seleccione una opción</option>
            @foreach ($menuItems as $menuItem)
                <option value="{{ $menuItem->idmenu }}" @selected((string) old('idmenu', $menuPermission->idmenu ?? '') === (string) $menuItem->idmenu)>{{ $menuItem->nombre }}</option>
            @endforeach
        </select>
    </div>

    @isset ($menuPermission)
        <label class="flex items-center gap-3 rounded-md border border-slate-200 px-3 py-3 text-sm">
            <input type="hidden" name="activo" value="0">
            <input name="activo" type="checkbox" value="1" @checked(old('activo', $menuPermission->activo)) class="size-4 rounded border-slate-300 text-sky-700 focus:ring-sky-600">
            <span>
                <span class="block font-medium text-slate-800">Permiso activo</span>
                <span class="block text-slate-500">Un permiso revocado no será expuesto a Power BI.</span>
            </span>
        </label>
    @endisset

    <div class="flex flex-wrap items-center gap-3 pt-2">
        <button type="submit" class="rounded-md bg-sky-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-800">Guardar</button>
        <a href="{{ route('menu-permissions.index') }}" class="rounded-md px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancelar</a>
    </div>
</div>
