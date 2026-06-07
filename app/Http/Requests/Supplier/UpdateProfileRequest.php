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

    protected function prepareForValidation(): void
    {
        $rfc = $this->input('rfc');
        $this->merge([
            'rfc' => filled($rfc) ? strtoupper(trim((string) $rfc)) : null,
        ]);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'rfc' => [
                'nullable',
                'string',
                'max:13',
                'regex:/^[A-ZÑ&]{3,4}[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])[A-Z0-9]{3}$/i',
                Rule::unique('suppliers', 'rfc')->ignore($this->user('supplier')->id),
            ],
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
            'rfc.regex' => 'El RFC no tiene un formato válido.',
            'rfc.unique' => 'Este RFC ya está registrado.',
            'rfc.max' => 'El RFC no puede tener más de 13 caracteres.',
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
