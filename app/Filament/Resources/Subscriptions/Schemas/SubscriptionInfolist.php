<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Subscription')
                    ->schema([
                        TextEntry::make('user.email')
                            ->copyable(),
                        TextEntry::make('plan')
                            ->badge(),
                        TextEntry::make('status')
                            ->badge(),
                        TextEntry::make('provider'),
                        TextEntry::make('provider_subscription_id')
                            ->copyable()
                            ->placeholder('None'),
                        TextEntry::make('monthly_request_limit')
                            ->numeric()
                            ->placeholder('Unlimited'),
                        TextEntry::make('trial_ends_at')
                            ->dateTime()
                            ->placeholder('None'),
                        TextEntry::make('ends_at')
                            ->dateTime()
                            ->placeholder('None'),
                    ])
                    ->columns(2),
            ]);
    }
}
