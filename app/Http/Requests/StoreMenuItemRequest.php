<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->get('pbi_admin_authenticated') === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'pagina_destino' => ['required', 'string', 'max:255'],
            'orden' => ['required', 'integer', 'between:0,9999'],
            'activo' => ['required', 'boolean'],
        ];
    }
}
