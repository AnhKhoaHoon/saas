<?php

namespace App\Filament\Resources\UsageLogs\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UsageLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Request')
                    ->schema([
                        TextEntry::make('project.name'),
                        TextEntry::make('apiKey.name')
                            ->label('API key'),
                        TextEntry::make('request_id')
                            ->copyable()
                            ->placeholder('None'),
                        TextEntry::make('method')
                            ->badge(),
                        TextEntry::make('endpoint')
                            ->copyable(),
                        TextEntry::make('status_code')
                            ->badge(),
                        TextEntry::make('response_time_ms')
                            ->numeric(),
                        TextEntry::make('response_size_bytes')
                            ->numeric(),
                        TextEntry::make('ip_address')
                            ->copyable()
                            ->placeholder('None'),
                        TextEntry::make('occurred_at')
                            ->dateTime(),
                        KeyValueEntry::make('meta')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
