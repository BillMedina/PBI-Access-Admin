@extends('layouts.app')

@section('title', 'Editar opción · Administración PBI')

@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('menu-items.index') }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">← Volver al menú</a>
        <h1 class="mt-4 text-2xl font-bold tracking-tight">Editar opción del menú general</h1>

        <form method="POST" action="{{ route('menu-items.update', $menuItem) }}" class="mt-7 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            @csrf
            @method('PUT')
            @include('menu-items._form')
        </form>
    </div>
@endsection
