@extends('layouts.app')

@section('title', 'Nueva opción · Administración PBI')

@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('menu-items.index') }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">← Volver al menú</a>
        <h1 class="mt-4 text-2xl font-bold tracking-tight">Nueva opción del menú general</h1>
        <p class="mt-2 text-sm text-slate-600">La opción requerirá una asignación individual antes de aparecer para un usuario de Power BI.</p>

        <form method="POST" action="{{ route('menu-items.store') }}" class="mt-7 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            @csrf
            @include('menu-items._form')
        </form>
    </div>
@endsection
