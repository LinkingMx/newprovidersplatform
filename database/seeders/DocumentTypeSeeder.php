<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'nombre' => 'Constancia de situación fiscal',
                'descripcion' => 'Documento emitido por el SAT que acredita la situación fiscal del contribuyente.',
                'validez_dias' => null,
                'activo' => true,
            ],
            [
                'nombre' => 'Comprobante de domicilio',
                'descripcion' => 'Recibo de servicios (luz, agua, teléfono) con antigüedad no mayor a 3 meses.',
                'validez_dias' => 90,
                'activo' => true,
            ],
        ];

        foreach ($types as $type) {
            DocumentType::firstOrCreate(
                ['nombre' => $type['nombre']],
                $type
            );
        }
    }
}
