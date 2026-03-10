<?php

namespace App\Filament\Resources\BranchRequests\Pages;

use App\Filament\Resources\BranchRequests\BranchRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListBranchRequests extends ListRecords
{
    protected static string $resource = BranchRequestResource::class;
}
