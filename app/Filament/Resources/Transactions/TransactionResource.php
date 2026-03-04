<?php

namespace App\Filament\Resources\Transactions;

use App\Enums\TransactionStatus;
use App\Filament\RelationManagers\NotesRelationManager;
use App\Filament\Resources\Donations\DonationResource;
use App\Models\Transaction;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return 'heroicon-o-credit-card';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Donations';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transaction Details')
                    ->schema([
                        Select::make('donation_id')
                            ->relationship('donation', 'id')
                            ->required()
                            ->searchable(),
                        TextInput::make('payment_intent_id')
                            ->label('Stripe Payment Intent')
                            ->disabled(),
                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->disabled()
                            ->prefix('$')
                            ->formatStateUsing(fn ($state) => number_format($state / 100, 2)),
                        Select::make('status')
                            ->options(TransactionStatus::class)
                            ->disabled(),
                        Toggle::make('livemode')
                            ->label('Live Mode')
                            ->disabled(),
                    ])
                    ->columns(2),

                Section::make('Stripe Payload')
                    ->schema([
                        KeyValue::make('payload')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['donation.donor']))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('donation.id')
                    ->label('Donation ID')
                    ->url(fn (Transaction $record): ?string => $record->donation_id ? DonationResource::getUrl('view', ['record' => $record->donation_id]) : null)
                    ->sortable(),
                TextColumn::make('donation.donor_name')
                    ->label('Donor')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('donation.donor', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('payment_intent_id')
                    ->label('Payment Intent')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('USD', divideBy: 100)
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                IconColumn::make('livemode')
                    ->label('Live')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(TransactionStatus::class),
                TernaryFilter::make('livemode')
                    ->label('Environment')
                    ->placeholder('All')
                    ->trueLabel('Live')
                    ->falseLabel('Test'),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            NotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'view' => Pages\ViewTransaction::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    /** @return Builder<Transaction> */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('donor')) {
            $query->whereHas('donation.donor', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        return $query;
    }
}
