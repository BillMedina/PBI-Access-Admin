<?php

namespace App\Http\Requests;

use App\Models\SecurityCompany;
use App\Models\SecurityProfile;
use App\Models\SecurityUser;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSecurityUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->get('pbi_admin_authenticated') === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombre' => trim((string) $this->input('nombre')),
            'correo' => Str::lower(trim((string) $this->input('correo'))),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $connection = config('access-control.connection');
        $securityUser = $this->route('securityUser');
        $userId = $securityUser instanceof SecurityUser ? $securityUser->getKey() : null;

        return [
            'nombre' => ['nullable', 'string', 'max:150'],
            'correo' => [
                'required',
                'email',
                'max:255',
                Rule::unique($connection.'.pbi_security_users', 'correo')->ignore($userId, 'idusuario'),
            ],
            'idperfil' => ['required', 'integer', 'exists:'.$connection.'.pbi_security_profiles,idperfil'],
            'company_ids' => ['required', 'array', 'min:1'],
            'company_ids.*' => ['integer', 'distinct', 'exists:'.$connection.'.pbi_security_companies,idempresa'],
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
                if (! $validator->errors()->has('idperfil')
                    && ! SecurityProfile::query()->active()->whereKey($this->integer('idperfil'))->exists()) {
                    $validator->errors()->add('idperfil', 'Seleccione un perfil activo.');
                }

                if ($validator->errors()->has('company_ids')) {
                    return;
                }

                $companyIds = collect($this->input('company_ids', []))
                    ->map(fn (mixed $id): int => (int) $id)
                    ->unique();
                $activeCompanyCount = SecurityCompany::query()
                    ->active()
                    ->whereIn('idempresa', $companyIds)
                    ->count();

                if ($activeCompanyCount !== $companyIds->count()) {
                    $validator->errors()->add('company_ids', 'Seleccione únicamente empresas activas.');
                }
            },
        ];
    }
}
