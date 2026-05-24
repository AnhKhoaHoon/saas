<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuditLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Audit event')
                    ->schema([
                        TextEntry::make('action')
                            ->badge(),
                        TextEntry::make('user.email')
                            ->label('Actor')
                            ->placeholder('System'),
                        TextEntry::make('project.name')
                            ->placeholder('Global'),
                        TextEntry::make('description')
                            ->columnSpanFull(),
                        TextEntry::make('auditable_type'),
                        TextEntry::make('auditable_id'),
                        TextEntry::make('ip_address')
                            ->copyable()
                            ->placeholder('None'),
                        TextEntry::make('user_agent')
                            ->columnSpanFull()
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
