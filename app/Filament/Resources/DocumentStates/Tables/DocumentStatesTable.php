<?php

namespace App\Filament\Resources\DocumentStates\Tables;

use App\States\DocumentState;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class DocumentStatesTable
{
    public static function configure(Table $table): Table
    {
        $records = self::getStateRecords();

        return $table
            ->query(self::createQuery($records))
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),

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

    private static function getStateRecords(): Collection
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

        return $records;
    }

    private static function createQuery(Collection $records)
    {
        return app(\Illuminate\Database\Query\Builder::class)
            ->select('*')
            ->from('_states')
            ->getQuery()
            ->setBindings([])
            ->getModel()
            ->hydrate($records->all());
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
