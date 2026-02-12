<?php

namespace Database\Factories;

use App\Models\DocumentState;
use App\Models\DocumentType;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupplierDocument>
 */
class SupplierDocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'document_type_id' => DocumentType::factory(),
            'document_state_id' => fn () => DocumentState::where('por_defecto', true)->value('id') ?? 1,
        ];
    }

    public function uploaded(): static
    {
        return $this->state(fn () => [
            'archivo_path' => 'documents/'.fake()->uuid().'.pdf',
            'archivo_nombre' => fake()->word().'.pdf',
            'uploaded_at' => now(),
        ]);
    }

    public function withExpiry(): static
    {
        return $this->state(fn () => [
            'fecha_vencimiento' => fake()->dateTimeBetween('+1 month', '+1 year'),
        ]);
    }
}
