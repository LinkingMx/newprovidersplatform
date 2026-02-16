<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use App\Models\ProviderType;
use Illuminate\Database\Seeder;

class ProviderTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'nombre' => 'Persona Física con Actividad Empresarial',
                'descripcion' => 'Persona física que realiza actividades empresariales o profesionales.',
                'activo' => true,
            ],
            [
                'nombre' => 'Persona Moral',
                'descripcion' => 'Entidad jurídica constituida conforme a las leyes mexicanas.',
                'activo' => true,
            ],
        ];

        $documentTypes = DocumentType::where('activo', true)->get();

        foreach ($types as $type) {
            $providerType = ProviderType::firstOrCreate(
                ['nombre' => $type['nombre']],
                $type
            );

            // Attach all active document types as obligatory (skip if already attached)
            foreach ($documentTypes as $documentType) {
                $providerType->documentTypes()->syncWithoutDetaching([
                    $documentType->id => ['obligatorio' => true],
                ]);
            }
        }
    }
}
