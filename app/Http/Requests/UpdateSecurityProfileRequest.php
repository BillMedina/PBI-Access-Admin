<?php

namespace App\Http\Requests;

use App\Models\MenuItem;
use App\Models\SecurityProfile;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSecurityProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->get('pbi_admin_authenticated') === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombre' => trim((string) $this->input('nombre')),
            'powerbi_role_name' => trim((string) $this->input('powerbi_role_name')),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $connection = config('access-control.connection');
        $profile = $this->route('securityProfile');
        $profileId = $profile instanceof SecurityProfile ? $profile->getKey() : null;

        return [
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique($connection.'.pbi_security_profiles', 'nombre')->ignore($profileId, 'idperfil'),
            ],
            'powerbi_role_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique($connection.'.pbi_security_profiles', 'powerbi_role_name')->ignore($profileId, 'idperfil'),
            ],
            'menu_item_ids' => ['nullable', 'array'],
            'menu_item_ids.*' => ['integer', 'distinct', 'exists:'.$connection.'.pbi_menu_items,idmenu'],
            'activo' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('menu_item_ids')) {
                    return;
                }

                $menuItemIds = collect($this->input('menu_item_ids', []))
                    ->map(fn (mixed $id): int => (int) $id)
                    ->unique();

                if ($menuItemIds->isEmpty()) {
                    return;
                }

                $availableCount = MenuItem::query()
                    ->whereIn('idmenu', $menuItemIds)
                    ->where('activo', true)
                    ->where('requiere_permiso', true)
                    ->count();

                if ($availableCount !== $menuItemIds->count()) {
                    $validator->errors()->add('menu_item_ids', 'Seleccione únicamente opciones activas que requieran permiso.');
                }
            },
        ];
    }
}
