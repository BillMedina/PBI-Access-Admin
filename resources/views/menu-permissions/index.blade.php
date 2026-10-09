@extends('layouts.app')

@section('title', 'Permisos por usuario · Administración PBI')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-sky-700">Versión 1</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight">Excepciones de menú</h1>
            <p class="mt-2 text-sm text-slate-600">Estas asignaciones se agregan a los menús definidos por perfil; úselas solo para excepciones individuales.</p>
        </div>
        <a href="{{ route('menu-permissions.create') }}" class="rounded-md bg-sky-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-800">Asignar excepción</a>
    </div>

    <div class="mt-7 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th scope="col" class="px-4 py-3">Correo</th>
                        <th scope="col" class="px-4 py-3">Opción de menú</th>
                        <th scope="col" class="px-4 py-3">Estado</th>
                        <th scope="col" class="px-4 py-3"><span class="sr-only">Acciones</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($permissions as $permission)
                        <tr class="{{ $permission->activo ? 'bg-white' : 'bg-slate-50 text-slate-500' }}">
                            <td class="px-4 py-3 font-medium">{{ $permission->correo }}</td>
                            <td class="px-4 py-3">{{ $permission->menuItem?->nombre ?? 'Opción eliminada' }}</td>
                            <td class="px-4 py-3">
                                @if ($permission->activo)
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800">Activo</span>
                                @else
                                    <span class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700">Revocado</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('menu-permissions.edit', $permission) }}" class="font-medium text-sky-700 hover:text-sky-900">Editar</a>
                                    @if ($permission->activo)
                                        <form method="POST" action="{{ route('menu-permissions.destroy', $permission) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-medium text-rose-700 hover:text-rose-900">Revocar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-slate-500">No existen permisos asignados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($permissions->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">
                {{ $permissions->links() }}
            </div>
        @endif
    </div>
@endsection
