<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Administración de accesos PBI')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900">
    <header class="border-b border-slate-800 bg-slate-900 text-slate-100">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <a href="{{ route('security-users.index') }}" class="font-semibold tracking-tight">Administración PBI</a>

            <nav class="flex flex-wrap items-center gap-2 text-sm" aria-label="Navegación principal">
                <a href="{{ route('security-users.index') }}" @class([
                    'rounded-md px-3 py-2 transition',
                    'bg-slate-700 text-white' => request()->routeIs('security-users.*'),
                    'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('security-users.*'),
                ])>Usuarios</a>
                <a href="{{ route('security-profiles.index') }}" @class([
                    'rounded-md px-3 py-2 transition',
                    'bg-slate-700 text-white' => request()->routeIs('security-profiles.*'),
                    'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('security-profiles.*'),
                ])>Perfiles</a>
                <a href="{{ route('security-companies.index') }}" @class([
                    'rounded-md px-3 py-2 transition',
                    'bg-slate-700 text-white' => request()->routeIs('security-companies.*'),
                    'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('security-companies.*'),
                ])>Empresas</a>
                <span class="hidden h-6 w-px bg-slate-700 sm:block" aria-hidden="true"></span>
                <a href="{{ route('menu-items.index') }}" @class([
                    'rounded-md px-3 py-2 transition',
                    'bg-slate-700 text-white' => request()->routeIs('menu-items.*'),
                    'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('menu-items.*'),
                ])>Menú</a>
                <a href="{{ route('menu-permissions.index') }}" @class([
                    'rounded-md px-3 py-2 transition',
                    'bg-slate-700 text-white' => request()->routeIs('menu-permissions.*'),
                    'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('menu-permissions.*'),
                ])>Excepciones</a>
                <form method="POST" action="{{ route('admin-session.destroy') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-md px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white">Salir</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        @if (session('status'))
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                <p class="font-semibold">Revise los datos ingresados.</p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
