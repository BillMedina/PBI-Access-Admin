@extends('layouts.app')

@section('title', 'Editar usuario RLS · Administración PBI')

@section('content')
    <div class="max-w-3xl">
        <a href="{{ route('security-users.index') }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">← Volver a usuarios</a>
        <h1 class="mt-4 text-2xl font-bold tracking-tight">Editar usuario de seguridad</h1>
        <p class="mt-2 text-sm text-slate-600">Cambiar el perfil aquí requiere la misma actualización de membresía en Power BI Service o Entra ID.</p>

        <form method="POST" action="{{ route('security-users.update', $securityUser) }}" class="mt-7 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            @csrf
            @method('PUT')
            @include('security-users._form')
        </form>
    </div>
@endsection
