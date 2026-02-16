<?php

declare(strict_types=1);

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $supplier = $this->user('supplier');

        return $supplier && $supplier->status->value !== 'created' && $supplier->status->value !== 'invited';
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'address_street' => ['required', 'string', 'max:255'],
            'address_number' => ['required', 'string', 'max:50'],
            'address_neighborhood' => ['required', 'string', 'max:255'],
            'address_city' => ['required', 'string', 'max:255'],
            'address_country' => ['required', 'string', 'in:Mexico,USA,Canada'],
            'address_zip' => ['required', 'string', 'regex:/^\d{5}$/'],
            'clabe_interbancaria' => [
                'required',
                'string',
                'regex:/^\d{18}$/',
                Rule::unique('suppliers', 'clabe_interbancaria')->ignore($this->user('supplier')->id),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'address_street.required' => 'La calle es requerida.',
            'address_number.required' => 'El número es requerido.',
            'address_neighborhood.required' => 'La colonia es requerida.',
            'address_city.required' => 'La ciudad es requerida.',
            'address_country.required' => 'El país es requerido.',
            'address_zip.regex' => 'El código postal debe ser de 5 dígitos.',
            'clabe_interbancaria.required' => 'La CLABE es requerida.',
            'clabe_interbancaria.regex' => 'La CLABE debe ser exactamente 18 dígitos.',
            'clabe_interbancaria.unique' => 'Esta CLABE ya está registrada.',
        ];
    }
}
