@extends('layouts.app')

@section('title', 'Editar empresa RLS · Administración PBI')

@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('security-companies.index') }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">← Volver a empresas</a>
        <h1 class="mt-4 text-2xl font-bold tracking-tight">Editar empresa</h1>

        <form method="POST" action="{{ route('security-companies.update', $securityCompany) }}" class="mt-7 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            @csrf
            @method('PUT')
            @include('security-companies._form')
        </form>
    </div>
@endsection
