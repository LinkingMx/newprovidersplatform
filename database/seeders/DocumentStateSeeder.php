<?php

namespace Database\Seeders;

use App\Models\DocumentState;
use Illuminate\Database\Seeder;

class DocumentStateSeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            [
                'nombre' => 'Pendiente',
                'etiqueta' => 'Pendiente',
                'color' => 'gray',
                'icono' => 'heroicon-o-clock',
                'por_defecto' => true,
                'completado' => false,
                'transiciones_permitidas' => ['En Revisión'],
            ],
            [
                'nombre' => 'En Revisión',
                'etiqueta' => 'En Revisión',
                'color' => 'blue',
                'icono' => 'heroicon-o-eye',
                'por_defecto' => false,
                'completado' => false,
                'transiciones_permitidas' => ['Aprobado', 'Rechazado'],
            ],
            [
                'nombre' => 'Aprobado',
                'etiqueta' => 'Aprobado',
                'color' => 'green',
                'icono' => 'heroicon-o-check-circle',
                'por_defecto' => false,
                'completado' => true,
                'transiciones_permitidas' => ['Rechazado'],
            ],
            [
                'nombre' => 'Rechazado',
                'etiqueta' => 'Rechazado',
                'color' => 'red',
                'icono' => 'heroicon-o-x-circle',
                'por_defecto' => false,
                'completado' => false,
                'transiciones_permitidas' => ['En Revisión'],
            ],
        ];

        foreach ($states as $state) {
            DocumentState::firstOrCreate(
                ['nombre' => $state['nombre']],
                $state
            );
        }
    }
}
