<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class SupplierDocumentUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $supplier = auth('supplier')->user();
        $document = $this->route('supplierDocument');

        if (! $supplier || ! $document) {
            return false;
        }

        // Verify ownership
        if ($document->supplier_id !== $supplier->id) {
            return false;
        }

        // Only allow upload when state is "Pendiente" (1) or "Rechazado" (4)
        return in_array($document->document_state_id, [1, 4]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'archivo.required' => 'Debes seleccionar un archivo para subir.',
            'archivo.file' => 'El archivo no es válido.',
            'archivo.mimes' => 'El archivo debe ser PDF, JPG o PNG.',
            'archivo.max' => 'El archivo no debe superar los 10 MB.',
        ];
    }
}
