<?php

namespace App\Http\Requests;

use App\Models\MenuItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class StoreMenuUserPermissionRequest extends FormRequest
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

        return [
            'correo' => ['required', 'email', 'max:255'],
            'idmenu' => ['required', 'integer', 'exists:'.$connection.'.pbi_menu_items,idmenu'],
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
                    ->where('activo', true)
                    ->where('requiere_permiso', true)
                    ->exists();

                if (! $isAvailable) {
                    $validator->errors()->add('idmenu', 'Seleccione una opción activa del menú general.');
                }
            },
        ];
    }
}
