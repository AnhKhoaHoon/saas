<?php

namespace App\Filament\Resources\TeamInvites;

use App\Filament\Resources\TeamInvites\Pages\CreateTeamInvite;
use App\Filament\Resources\TeamInvites\Pages\EditTeamInvite;
use App\Filament\Resources\TeamInvites\Pages\ListTeamInvites;
use App\Filament\Resources\TeamInvites\Pages\ViewTeamInvite;
use App\Filament\Resources\TeamInvites\Schemas\TeamInviteForm;
use App\Filament\Resources\TeamInvites\Schemas\TeamInviteInfolist;
use App\Filament\Resources\TeamInvites\Tables\TeamInvitesTable;
use App\Models\TeamInvite;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TeamInviteResource extends Resource
{
    protected static ?string $model = TeamInvite::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = 'Access';

    protected static ?int $navigationSort = 70;

    protected static ?string $recordTitleAttribute = 'email';

    public static function form(Schema $schema): Schema
    {
        return TeamInviteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TeamInviteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TeamInvitesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeamInvites::route('/'),
            'create' => CreateTeamInvite::route('/create'),
            'view' => ViewTeamInvite::route('/{record}'),
            'edit' => EditTeamInvite::route('/{record}/edit'),
        ];
    }
}
