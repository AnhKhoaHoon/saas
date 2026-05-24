<?php

namespace App\Filament\Resources\TeamInvites\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeamInviteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invite')
                    ->schema([
                        TextEntry::make('email')
                            ->copyable(),
                        TextEntry::make('project.name'),
                        TextEntry::make('role')
                            ->badge(),
                        TextEntry::make('inviter.email')
                            ->label('Invited by')
                            ->placeholder('System'),
                        TextEntry::make('token')
                            ->copyable(),
                        TextEntry::make('accepted_at')
                            ->dateTime()
                            ->placeholder('Pending'),
                        TextEntry::make('expires_at')
                            ->dateTime()
                            ->placeholder('No expiry'),
                    ])
                    ->columns(2),
            ]);
    }
}
