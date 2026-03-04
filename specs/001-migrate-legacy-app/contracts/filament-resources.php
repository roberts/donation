<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class DonationResource extends Resource
{
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('school_id')
                ->relationship('school', 'name')
                ->required(),
            Forms\Components\TextInput::make('amount_cents')
                ->numeric()
                ->label('Amount (Cents)'),
            Forms\Components\TextInput::make('donor_email')
                ->email(),
            Forms\Components\TextInput::make('filing_year')
                ->numeric(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('donor_name')->searchable(),
            Tables\Columns\TextColumn::make('amount_cents')->money('USD', divideBy: 100),
            Tables\Columns\TextColumn::make('school.name'),
            Tables\Columns\TextColumn::make('filing_year')->sortable(),
        ])
            ->filters([
                Tables\Filters\SelectFilter::make('school')
                    ->relationship('school', 'name'),
                Tables\Filters\SelectFilter::make('filing_year')
                    ->options(fn () => range(date('Y'), 2020)),
            ])
            ->actions([
                Tables\Actions\Action::make('view_receipt')
                    ->label('View Receipt')
                    ->icon('heroicon-o-document-text')
                    ->url(fn ($record) => route('receipt.show', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('email_receipt')
                    ->label('Email Receipt')
                    ->icon('heroicon-o-envelope')
                    ->requiresConfirmation()
                    ->action(fn ($record) => \App\Jobs\SendDonationReceipt::dispatch($record)),
            ]);
    }
}
