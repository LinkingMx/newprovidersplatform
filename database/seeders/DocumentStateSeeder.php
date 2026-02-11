<?php

namespace Database\Seeders;

use App\Models\DocumentState;
use Illuminate\Database\Seeder;

class DocumentStateSeeder extends Seeder
{
    public function run(): void
    {
        DocumentState::create([
            'nombre' => 'Pendiente',
            'etiqueta' => 'Pendiente',
            'color' => 'gray',
            'icono' => 'heroicon-o-clock',
            'por_defecto' => true,
            'completado' => false,
            'transiciones_permitidas' => ['En Revisión'],
        ]);

        DocumentState::create([
            'nombre' => 'En Revisión',
            'etiqueta' => 'En Revisión',
            'color' => 'blue',
            'icono' => 'heroicon-o-eye',
            'por_defecto' => false,
            'completado' => false,
            'transiciones_permitidas' => ['Aprobado', 'Rechazado'],
        ]);

        DocumentState::create([
            'nombre' => 'Aprobado',
            'etiqueta' => 'Aprobado',
            'color' => 'green',
            'icono' => 'heroicon-o-check-circle',
            'por_defecto' => false,
            'completado' => true,
            'transiciones_permitidas' => ['Rechazado'],
        ]);

        DocumentState::create([
            'nombre' => 'Rechazado',
            'etiqueta' => 'Rechazado',
            'color' => 'red',
            'icono' => 'heroicon-o-x-circle',
            'por_defecto' => false,
            'completado' => false,
            'transiciones_permitidas' => ['En Revisión'],
        ]);
    }
}
