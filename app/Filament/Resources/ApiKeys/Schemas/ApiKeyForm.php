<?php

namespace App\Filament\Resources\ApiKeys\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ApiKeyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('API key')
                    ->schema([
                        Select::make('project_id')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('created_by')
                            ->label('Created by')
                            ->relationship('creator', 'email')
                            ->searchable()
                            ->preload(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'revoked' => 'Revoked',
                            ])
                            ->required(),
                        TextInput::make('rate_limit_per_minute')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        TextInput::make('quota_limit')
                            ->numeric()
                            ->minValue(1),
                        TagsInput::make('scopes')
                            ->placeholder('read'),
                        TagsInput::make('ip_whitelist')
                            ->label('IP whitelist'),
                        DateTimePicker::make('expires_at'),
                        DateTimePicker::make('revoked_at'),
                    ])
                    ->columns(2),
            ]);
    }
}
