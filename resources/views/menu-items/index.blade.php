@extends('layouts.app')

@section('title', 'Menú general · Administración PBI')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-sky-700">Versión 1</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight">Menú general</h1>
            <p class="mt-2 text-sm text-slate-600">Opciones visibles para el reporte. Las bajas son lógicas y se conservan en auditoría.</p>
        </div>
        <a href="{{ route('menu-items.create') }}" class="rounded-md bg-sky-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-800">Nueva opción</a>
    </div>

    <div class="mt-7 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th scope="col" class="px-4 py-3">Orden</th>
                        <th scope="col" class="px-4 py-3">Nombre</th>
                        <th scope="col" class="px-4 py-3">Destino Power BI</th>
                        <th scope="col" class="px-4 py-3">Estado</th>
                        <th scope="col" class="px-4 py-3"><span class="sr-only">Acciones</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($menuItems as $menuItem)
                        <tr class="{{ $menuItem->activo ? 'bg-white' : 'bg-slate-50 text-slate-500' }}">
                            <td class="px-4 py-3 font-mono text-xs">{{ $menuItem->orden }}</td>
                            <td class="px-4 py-3 font-medium">{{ $menuItem->nombre }}</td>
                            <td class="px-4 py-3">{{ $menuItem->pagina_destino }}</td>
                            <td class="px-4 py-3">
                                @if ($menuItem->isDefault())
                                    <span class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700">Predeterminado</span>
                                @elseif ($menuItem->activo)
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800">Activo</span>
                                @else
                                    <span class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @unless ($menuItem->isDefault())
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('menu-items.edit', $menuItem) }}" class="font-medium text-sky-700 hover:text-sky-900">Editar</a>
                                        @if ($menuItem->activo)
                                            <form method="POST" action="{{ route('menu-items.destroy', $menuItem) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-rose-700 hover:text-rose-900">Deshabilitar</button>
                                            </form>
                                        @endif
                                    </div>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-slate-500">No existen opciones configuradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($menuItems->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">
                {{ $menuItems->links() }}
            </div>
        @endif
    </div>
@endsection
