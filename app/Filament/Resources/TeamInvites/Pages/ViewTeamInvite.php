<?php

namespace App\Filament\Resources\TeamInvites\Pages;

use App\Filament\Resources\TeamInvites\TeamInviteResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTeamInvite extends ViewRecord
{
    protected static string $resource = TeamInviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
