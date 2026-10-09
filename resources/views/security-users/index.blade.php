@extends('layouts.app')

@section('title', 'Usuarios RLS · Administración PBI')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-sky-700">Seguridad Power BI</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight">Usuarios y empresas</h1>
            <p class="mt-2 text-sm text-slate-600">Cada usuario tiene un único perfil RLS y una o más empresas autorizadas.</p>
        </div>
        <a href="{{ route('security-users.create') }}" class="rounded-md bg-sky-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-800">Nuevo usuario</a>
    </div>

    <div class="mt-7 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th scope="col" class="px-4 py-3">Usuario</th>
                        <th scope="col" class="px-4 py-3">Correo</th>
                        <th scope="col" class="px-4 py-3">Perfil</th>
                        <th scope="col" class="px-4 py-3">Empresas</th>
                        <th scope="col" class="px-4 py-3">Estado</th>
                        <th scope="col" class="px-4 py-3"><span class="sr-only">Acciones</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($securityUsers as $securityUser)
                        <tr class="{{ $securityUser->activo ? 'bg-white' : 'bg-slate-50 text-slate-500' }}">
                            <td class="px-4 py-3 font-medium">{{ $securityUser->nombre ?: '—' }}</td>
                            <td class="px-4 py-3">{{ $securityUser->correo }}</td>
                            <td class="px-4 py-3">
                                <span class="font-medium">{{ $securityUser->profile?->nombre ?? 'Sin perfil' }}</span>
                                @if ($securityUser->profile)
                                    <span class="block font-mono text-xs text-slate-500">{{ $securityUser->profile->codigo }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex max-w-sm flex-wrap gap-1.5">
                                    @forelse ($securityUser->companyAssignments as $assignment)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700">{{ $assignment->company?->nombre }}</span>
                                    @empty
                                        <span class="text-slate-500">Sin empresas</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if ($securityUser->activo)
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800">Activo</span>
                                @else
                                    <span class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('security-users.edit', $securityUser) }}" class="font-medium text-sky-700 hover:text-sky-900">Editar</a>
                                    @if ($securityUser->activo)
                                        <form method="POST" action="{{ route('security-users.destroy', $securityUser) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-medium text-rose-700 hover:text-rose-900">Desactivar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-500">No existen usuarios de seguridad configurados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($securityUsers->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">
                {{ $securityUsers->links() }}
            </div>
        @endif
    </div>
@endsection
