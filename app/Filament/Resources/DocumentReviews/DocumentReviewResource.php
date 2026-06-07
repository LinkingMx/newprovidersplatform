<?php

namespace App\Filament\Resources\DocumentReviews;

use App\Filament\Resources\DocumentReviews\Pages\ListDocumentReviews;
use App\Filament\Resources\DocumentReviews\Tables\DocumentReviewsTable;
use App\Models\DocumentState;
use App\Models\SupplierDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class DocumentReviewResource extends Resource
{
    protected static ?string $model = SupplierDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Proveedores';

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return 'Revisión de Documentos';
    }

    public static function getModelLabel(): string
    {
        return 'documento';
    }

    public static function getPluralModelLabel(): string
    {
        return 'documentos';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = SupplierDocument::query()
            ->where('document_state_id', DocumentState::EN_REVISION)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Documentos en revisión';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'supplier:id,name,email',
                'documentType:id,nombre',
                'documentState:id,nombre,etiqueta,color,icono,transiciones_permitidas',
                'reviewer:id,name',
            ]);
    }

    public static function table(Table $table): Table
    {
        return DocumentReviewsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentReviews::route('/'),
        ];
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
}
