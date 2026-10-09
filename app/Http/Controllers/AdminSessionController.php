<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminSessionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminSessionController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('pbi_admin_authenticated') === true) {
            return redirect()->route('menu-items.index');
        }

        return view('auth.login');
    }

    public function store(StoreAdminSessionRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $configuredUsername = config('access-control.admin.username');
        $configuredPasswordHash = config('access-control.admin.password_hash');

        $isValidUsername = is_string($configuredUsername)
            && hash_equals(mb_strtolower($configuredUsername), $credentials['username']);
        $isValidPassword = is_string($configuredPasswordHash)
            && $configuredPasswordHash !== ''
            && Hash::check($credentials['password'], $configuredPasswordHash);

        if (! $isValidUsername || ! $isValidPassword) {
            throw ValidationException::withMessages([
                'username' => 'Las credenciales no son válidas.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('pbi_admin_authenticated', true);
        $request->session()->put('pbi_admin_username', $configuredUsername);

        return redirect()->intended(route('security-users.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin-session.create');
    }
}
