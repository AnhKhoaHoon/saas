<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Project')
                    ->schema([
                        Select::make('user_id')
                            ->label('Owner')
                            ->relationship('owner', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'archived' => 'Archived',
                                'suspended' => 'Suspended',
                            ])
                            ->required(),
                        Textarea::make('description')
                            ->columnSpanFull()
                            ->maxLength(1000),
                        KeyValue::make('settings')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
