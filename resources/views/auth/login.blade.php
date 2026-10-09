<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso administrativo PBI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 px-4 font-sans text-slate-900">
    <main class="w-full max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-sky-700">PBI Access</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight">Administración de menú</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Ingrese con la cuenta administrativa local configurada en el servidor.</p>

        @if ($errors->any())
            <div class="mt-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin-session.store') }}" class="mt-6 grid gap-5">
            @csrf
            <div class="grid gap-2">
                <label for="username" class="text-sm font-medium text-slate-700">Usuario</label>
                <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" required autofocus class="rounded-md border-slate-300 px-3 py-2 shadow-sm outline-none ring-sky-600 transition focus:ring-2">
            </div>
            <div class="grid gap-2">
                <label for="password" class="text-sm font-medium text-slate-700">Contraseña</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required class="rounded-md border-slate-300 px-3 py-2 shadow-sm outline-none ring-sky-600 transition focus:ring-2">
            </div>
            <button type="submit" class="rounded-md bg-sky-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-800 focus:outline-none focus:ring-2 focus:ring-sky-600 focus:ring-offset-2">Ingresar</button>
        </form>
    </main>
</body>
</html>
