<?php

namespace App\Filament\Resources\DocumentStates\Pages;

use App\Filament\Resources\DocumentStates\DocumentStateResource;
use Filament\Resources\Pages\ListRecords;

class ListDocumentStates extends ListRecords
{
    protected static string $resource = DocumentStateResource::class;

    public function getTitle(): string
    {
        return 'Estados De Documento';
    }
}
