<?php

namespace App\Filament\Resources\ProviderTypes;

use App\Filament\Resources\ProviderTypes\Pages\CreateProviderType;
use App\Filament\Resources\ProviderTypes\Pages\EditProviderType;
use App\Filament\Resources\ProviderTypes\Pages\ListProviderTypes;
use App\Filament\Resources\ProviderTypes\Schemas\ProviderTypeForm;
use App\Filament\Resources\ProviderTypes\Tables\ProviderTypesTable;
use App\Models\ProviderType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ProviderTypeResource extends Resource
{
    protected static ?string $model = ProviderType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $navigationLabel = 'Tipos de Proveedor';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Proveedores';

    protected static ?string $modelLabel = 'tipo de proveedor';

    protected static ?string $pluralModelLabel = 'tipos de proveedor';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ProviderTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProviderTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DocumentTypesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProviderTypes::route('/'),
            'create' => CreateProviderType::route('/create'),
            'edit' => EditProviderType::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
