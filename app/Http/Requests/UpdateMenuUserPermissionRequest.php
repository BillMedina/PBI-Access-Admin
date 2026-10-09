<?php

namespace App\Http\Requests;

use App\Models\MenuItem;
use App\Models\MenuUserPermission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateMenuUserPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->get('pbi_admin_authenticated') === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'correo' => Str::lower(trim((string) $this->input('correo'))),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $connection = config('access-control.connection');
        $permission = $this->route('menuPermission');
        $permissionId = $permission instanceof MenuUserPermission ? $permission->getKey() : null;

        return [
            'correo' => [
                'required',
                'email',
                'max:255',
                Rule::unique($connection.'.pbi_menu_user_permissions', 'correo')
                    ->where('idmenu', $this->integer('idmenu'))
                    ->ignore($permissionId, 'idmenu_permiso'),
            ],
            'idmenu' => ['required', 'integer', 'exists:'.$connection.'.pbi_menu_items,idmenu'],
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
                if ($validator->errors()->has('idmenu')) {
                    return;
                }

                $isAvailable = MenuItem::query()
                    ->whereKey($this->integer('idmenu'))
                    ->where('modulo', 'GENERAL')
                    ->where('requiere_permiso', true)
                    ->exists();

                if (! $isAvailable) {
                    $validator->errors()->add('idmenu', 'Seleccione una opción del menú general.');
                }
            },
        ];
    }
}
