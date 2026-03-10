<?php

namespace App\Filament\Resources\BranchRequests;

use App\Enums\BranchRequestStatus;
use App\Filament\Resources\BranchRequests\Pages\ListBranchRequests;
use App\Filament\Resources\BranchRequests\Pages\ViewBranchRequest;
use App\Filament\Resources\BranchRequests\Tables\BranchRequestsTable;
use App\Models\BranchRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BranchRequestResource extends Resource
{
    protected static ?string $model = BranchRequest::class;

    protected static ?string $navigationLabel = 'Solicitudes de Sucursal';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Proveedores';

    protected static ?string $modelLabel = 'solicitud de sucursal';

    protected static ?string $pluralModelLabel = 'solicitudes de sucursal';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function table(Table $table): Table
    {
        return BranchRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBranchRequests::route('/'),
            'view' => ViewBranchRequest::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', BranchRequestStatus::Pending)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Solicitudes pendientes';
    }
}
