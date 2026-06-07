<?php

namespace App\Filament\Resources\Activities\Pages;

use App\Filament\Resources\Activities\ActivityResource;
use Filament\Resources\Pages\ViewRecord;

class ViewActivity extends ViewRecord
{
    protected static string $resource = ActivityResource::class;

    public function getTitle(): string
    {
        return 'Ver registro de actividad';
    }

    public function getHeading(): string
    {
        return 'Ver registro de actividad';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        $data['causer_name'] = $record->causer?->name ?? 'Sistema';

        $model = $record->subject_type ? class_basename($record->subject_type) : '—';
        $label = $record->subject?->name
            ?? $record->subject?->nombre
            ?? $record->subject?->title
            ?? $record->subject?->email;

        $data['subject_display'] = $model
            .($record->subject_id ? ' #'.$record->subject_id : '')
            .($label ? ' · '.$label : '');

        // Cargar properties.attributes para el KeyValueEntry
        $props = $record->properties ?? collect();
        $data['properties_attributes'] = $props->get('attributes', []);

        return $data;
    }
}
