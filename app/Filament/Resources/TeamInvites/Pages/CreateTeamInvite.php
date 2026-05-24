<?php

namespace App\Filament\Resources\TeamInvites\Pages;

use App\Filament\Resources\TeamInvites\TeamInviteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTeamInvite extends CreateRecord
{
    protected static string $resource = TeamInviteResource::class;
}
