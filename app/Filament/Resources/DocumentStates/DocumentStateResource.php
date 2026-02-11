<?php

namespace App\Filament\Resources\DocumentStates;

use App\Filament\Resources\DocumentStates\Pages\ListDocumentStates;
use App\Filament\Resources\DocumentStates\Tables\DocumentStatesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DocumentStateResource extends Resource
{
    protected static ?string $model = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static ?string $navigationLabel = 'Estados de Documento';

    protected static ?string $modelLabel = 'estado de documento';

    protected static ?string $pluralModelLabel = 'estados de documento';

    protected static ?int $navigationSort = 12;

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return DocumentStatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentStates::route('/'),
        ];
    }
}
