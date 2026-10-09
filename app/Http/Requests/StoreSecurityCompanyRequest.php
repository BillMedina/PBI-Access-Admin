<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSecurityCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->get('pbi_admin_authenticated') === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombre' => trim((string) $this->input('nombre')),
            'clave_pbi' => trim((string) $this->input('clave_pbi')),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $connection = config('access-control.connection');

        return [
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique($connection.'.pbi_security_companies', 'nombre'),
            ],
            'clave_pbi' => [
                'required',
                'string',
                'max:255',
                Rule::unique($connection.'.pbi_security_companies', 'clave_pbi'),
            ],
            'activo' => ['required', 'boolean'],
        ];
    }
}
