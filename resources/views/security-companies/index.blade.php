@extends('layouts.app')

@section('title', 'Empresas RLS · Administración PBI')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-sky-700">Seguridad Power BI</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight">Empresas</h1>
            <p class="mt-2 text-sm text-slate-600">La clave Power BI debe coincidir exactamente con el campo elegido en <code>Master_Dim_Empresa</code>.</p>
        </div>
        <a href="{{ route('security-companies.create') }}" class="rounded-md bg-sky-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-800">Nueva empresa</a>
    </div>

    <div class="mt-7 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th scope="col" class="px-4 py-3">Empresa</th>
                        <th scope="col" class="px-4 py-3">Clave Power BI</th>
                        <th scope="col" class="px-4 py-3">Usuarios activos</th>
                        <th scope="col" class="px-4 py-3">Estado</th>
                        <th scope="col" class="px-4 py-3"><span class="sr-only">Acciones</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($securityCompanies as $securityCompany)
                        <tr class="{{ $securityCompany->activo ? 'bg-white' : 'bg-slate-50 text-slate-500' }}">
                            <td class="px-4 py-3 font-medium">{{ $securityCompany->nombre }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $securityCompany->clave_pbi }}</td>
                            <td class="px-4 py-3">{{ $securityCompany->active_users_count }}</td>
                            <td class="px-4 py-3">
                                @if ($securityCompany->activo)
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800">Activa</span>
                                @else
                                    <span class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700">Inactiva</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('security-companies.edit', $securityCompany) }}" class="font-medium text-sky-700 hover:text-sky-900">Editar</a>
                                    @if ($securityCompany->activo)
                                        <form method="POST" action="{{ route('security-companies.destroy', $securityCompany) }}">
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
                            <td colspan="5" class="px-4 py-10 text-center text-slate-500">No existen empresas configuradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($securityCompanies->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">
                {{ $securityCompanies->links() }}
            </div>
        @endif
    </div>
@endsection
