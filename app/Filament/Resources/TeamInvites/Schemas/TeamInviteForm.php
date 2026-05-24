<?php

namespace App\Filament\Resources\TeamInvites\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TeamInviteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invite')
                    ->schema([
                        Select::make('project_id')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('invited_by')
                            ->label('Invited by')
                            ->relationship('inviter', 'email')
                            ->searchable()
                            ->preload(),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Select::make('role')
                            ->options([
                                'admin' => 'Admin',
                                'member' => 'Member',
                                'viewer' => 'Viewer',
                            ])
                            ->required(),
                        TextInput::make('token')
                            ->default(fn (): string => Str::random(48))
                            ->required()
                            ->maxLength(255),
                        DateTimePicker::make('expires_at'),
                        DateTimePicker::make('accepted_at'),
                    ])
                    ->columns(2),
            ]);
    }
}
