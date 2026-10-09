@extends('layouts.app')

@section('title', 'Editar permiso · Administración PBI')

@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('menu-permissions.index') }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">← Volver a permisos</a>
        <h1 class="mt-4 text-2xl font-bold tracking-tight">Editar permiso de menú</h1>

        <form method="POST" action="{{ route('menu-permissions.update', $menuPermission) }}" class="mt-7 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            @csrf
            @method('PUT')
            @include('menu-permissions._form')
        </form>
    </div>
@endsection
