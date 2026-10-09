@extends('layouts.app')

@section('title', 'Asignar permiso · Administración PBI')

@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('menu-permissions.index') }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">← Volver a permisos</a>
        <h1 class="mt-4 text-2xl font-bold tracking-tight">Asignar excepción de menú</h1>
        <p class="mt-2 text-sm text-slate-600">La excepción se suma a las opciones habilitadas por el perfil del usuario.</p>

        <form method="POST" action="{{ route('menu-permissions.store') }}" class="mt-7 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            @csrf
            @include('menu-permissions._form')
        </form>
    </div>
@endsection
