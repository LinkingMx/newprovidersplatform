<?php

namespace App\Filament\Resources\ProviderTypes\Pages;

use App\Filament\Resources\ProviderTypes\ProviderTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProviderType extends CreateRecord
{
    protected static string $resource = ProviderTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
