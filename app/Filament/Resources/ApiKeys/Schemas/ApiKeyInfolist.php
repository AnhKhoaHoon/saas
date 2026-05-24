<?php

namespace App\Filament\Resources\ApiKeys\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ApiKeyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('API key')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('project.name'),
                        TextEntry::make('creator.email')
                            ->label('Created by')
                            ->placeholder('System'),
                        TextEntry::make('key_prefix')
                            ->copyable(),
                        TextEntry::make('status')
                            ->badge(),
                        TextEntry::make('rate_limit_per_minute')
                            ->numeric(),
                        TextEntry::make('requests_count')
                            ->numeric(),
                        TextEntry::make('quota_limit')
                            ->numeric()
                            ->placeholder('Unlimited'),
                        TextEntry::make('last_used_at')
                            ->dateTime()
                            ->placeholder('Never'),
                        TextEntry::make('expires_at')
                            ->dateTime()
                            ->placeholder('Never'),
                    ])
                    ->columns(2),
            ]);
    }
}
