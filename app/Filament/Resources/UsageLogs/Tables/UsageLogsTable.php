<?php

namespace App\Filament\Resources\UsageLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsageLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('occurred_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('project.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('apiKey.name')
                    ->label('API key')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('method')
                    ->badge()
                    ->sortable(),
                TextColumn::make('endpoint')
                    ->searchable()
                    ->limit(48)
                    ->tooltip(fn ($record): string => $record->endpoint),
                TextColumn::make('status_code')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 500 ? 'danger' : ($state >= 400 ? 'warning' : 'success'))
                    ->sortable(),
                TextColumn::make('response_time_ms')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('method')
                    ->options([
                        'GET' => 'GET',
                        'POST' => 'POST',
                        'PUT' => 'PUT',
                        'PATCH' => 'PATCH',
                        'DELETE' => 'DELETE',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
