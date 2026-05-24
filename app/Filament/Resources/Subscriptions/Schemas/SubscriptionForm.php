<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Subscription')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('plan')
                            ->options([
                                'free' => 'Free',
                                'hobby' => 'Hobby',
                                'pro' => 'Pro',
                                'enterprise' => 'Enterprise',
                            ])
                            ->required(),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'trialing' => 'Trialing',
                                'past_due' => 'Past due',
                                'canceled' => 'Canceled',
                            ])
                            ->required(),
                        TextInput::make('provider')
                            ->maxLength(255),
                        TextInput::make('provider_subscription_id')
                            ->maxLength(255),
                        TextInput::make('project_limit')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('api_key_limit')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('monthly_request_limit')
                            ->numeric()
                            ->minValue(0),
                        DateTimePicker::make('trial_ends_at'),
                        DateTimePicker::make('ends_at'),
                    ])
                    ->columns(2),
            ]);
    }
}
