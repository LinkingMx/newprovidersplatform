<?php

declare(strict_types=1);

namespace App\Http\Requests\Supplier;

use App\Enums\BranchRequestStatus;
use App\Models\BranchRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBranchRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $supplier = $this->user('supplier');

        return $supplier && $supplier->canLogin();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'notas_proveedor' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'branch_id.required' => 'Debes seleccionar una sucursal.',
            'branch_id.exists' => 'La sucursal seleccionada no existe.',
            'notas_proveedor.max' => 'Las notas no pueden exceder 1000 caracteres.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $supplier = $this->user('supplier');
                $branchId = $this->input('branch_id');

                if (! $supplier || ! $branchId) {
                    return;
                }

                if ($supplier->branches()->where('branches.id', $branchId)->exists()) {
                    $validator->errors()->add('branch_id', 'Ya tienes asignada esta sucursal.');
                }

                $hasPending = BranchRequest::where('supplier_id', $supplier->id)
                    ->where('branch_id', $branchId)
                    ->where('status', BranchRequestStatus::Pending)
                    ->exists();

                if ($hasPending) {
                    $validator->errors()->add('branch_id', 'Ya tienes una solicitud pendiente para esta sucursal.');
                }
            },
        ];
    }
}
