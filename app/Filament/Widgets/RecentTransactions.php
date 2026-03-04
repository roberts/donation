<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentTransactions extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Transaction::query()->latest()->limit(10))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('F j, Y, g:i a')
                    ->sortable(),
                TextColumn::make('id')
                    ->label('Transaction ID')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
            ])
            ->paginated(false);
    }
}
