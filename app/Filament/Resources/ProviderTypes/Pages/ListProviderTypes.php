<?php

namespace App\Filament\Resources\ProviderTypes\Pages;

use App\Filament\Resources\ProviderTypes\ProviderTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProviderTypes extends ListRecords
{
    protected static string $resource = ProviderTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
