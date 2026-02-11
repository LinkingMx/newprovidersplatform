<?php

namespace App\Filament\Resources\DocumentStates\Tables;

use App\States\DocumentState;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentStatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(self::getStateQuery())
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre'),

                IconColumn::make('por_defecto')
                    ->label('Por defecto')
                    ->boolean(),

                IconColumn::make('completado')
                    ->label('Completado')
                    ->boolean(),

                TextColumn::make('transiciones')
                    ->label('Transiciones posibles')
                    ->html(),
            ]);
    }

    private static function getStateQuery()
    {
        $states = [
            DocumentState\Pendiente::class,
            DocumentState\EnRevision::class,
            DocumentState\Aprobado::class,
            DocumentState\Rechazado::class,
        ];

        $records = collect();

        foreach ($states as $stateClass) {
            $transiciones = self::getTransicionesHtml($stateClass);

            $records->push((object) [
                'id' => sha1($stateClass),
                'nombre' => $stateClass::getLabel(),
                'por_defecto' => $stateClass::isDefaultState(),
                'completado' => $stateClass::isCompletedState(),
                'transiciones' => $transiciones,
            ]);
        }

        // Create a dummy query that returns our records
        $query = \DB::table('_states')->limit(0); // Get the builder without actual query
        $query->getConnection()->getPdo(); // Ensure connection

        return new class($records)
        {
            private $records;

            public function __construct($records)
            {
                $this->records = $records;
            }

            public function get($columns = ['*'])
            {
                return $this->records;
            }

            public function __call($method, $arguments)
            {
                return $this;
            }
        };
    }

    private static function getTransicionesHtml(string $stateClass): string
    {
        $allStates = [
            DocumentState\Pendiente::class,
            DocumentState\EnRevision::class,
            DocumentState\Aprobado::class,
            DocumentState\Rechazado::class,
        ];

        $transiciones = [];
        $state = new $stateClass(null);

        foreach ($allStates as $target) {
            if ($state->canTransitionTo($target) && $target !== $stateClass) {
                $label = $target::getLabel();
                $transiciones[] = "<span class='inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200'>$label</span>";
            }
        }

        return implode(' ', $transiciones) ?: '—';
    }
}
