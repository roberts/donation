<?php

namespace App\Filament\Widgets;

use App\Models\Donation;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentDonations extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Donation::query()->latest()->limit(10))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('F j, Y, g:i a')
                    ->sortable(),
                TextColumn::make('id')
                    ->label('Donation ID')
                    ->searchable(),
                TextColumn::make('donor_name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('donor.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('USD', divideBy: 100)
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
