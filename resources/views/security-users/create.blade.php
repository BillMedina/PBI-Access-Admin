@extends('layouts.app')

@section('title', 'Nuevo usuario RLS · Administración PBI')

@section('content')
    <div class="max-w-3xl">
        <a href="{{ route('security-users.index') }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">← Volver a usuarios</a>
        <h1 class="mt-4 text-2xl font-bold tracking-tight">Nuevo usuario de seguridad</h1>
        <p class="mt-2 text-sm text-slate-600">La persona debe pertenecer al mismo perfil físico en Power BI Service o en su grupo Entra ID correspondiente.</p>

        <form method="POST" action="{{ route('security-users.store') }}" class="mt-7 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            @csrf
            @include('security-users._form')
        </form>
    </div>
@endsection
