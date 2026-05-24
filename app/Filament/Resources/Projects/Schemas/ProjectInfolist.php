<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Project')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('owner.email')
                            ->label('Owner')
                            ->copyable(),
                        TextEntry::make('slug')
                            ->copyable(),
                        TextEntry::make('status')
                            ->badge(),
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                        KeyValueEntry::make('settings')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
