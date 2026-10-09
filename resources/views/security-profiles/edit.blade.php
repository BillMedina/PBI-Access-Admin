@extends('layouts.app')

@section('title', 'Editar perfil RLS · Administración PBI')

@section('content')
    <div class="max-w-3xl">
        <a href="{{ route('security-profiles.index') }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">← Volver a perfiles</a>
        <h1 class="mt-4 text-2xl font-bold tracking-tight">Editar perfil RLS</h1>
        <p class="mt-2 text-sm text-slate-600">El código es inmutable para no romper los filtros publicados de Power BI.</p>

        <form method="POST" action="{{ route('security-profiles.update', $securityProfile) }}" class="mt-7 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            @csrf
            @method('PUT')
            @include('security-profiles._form')
        </form>
    </div>
@endsection
