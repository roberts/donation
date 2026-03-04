<?php

namespace App\Filament\Resources\Donations;

use App\Enums\DonationStatus;
use App\Enums\FilingStatus;
use App\Enums\PaymentMethod;
use App\Filament\RelationManagers\NotesRelationManager;
use App\Mail\DonationReceipt;
use App\Models\Donation;
use App\Models\Donor;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Spatie\LaravelPdf\Facades\Pdf;

class DonationResource extends Resource
{
    protected static ?string $model = Donation::class;

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return 'heroicon-o-currency-dollar';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Donations';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['id', 'donor.first_name', 'donor.last_name'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Donor & Contact Info')
                    ->schema([
                        Select::make('donor_id')
                            ->relationship('donor', 'last_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Section::make('Donor Details')
                                    ->schema([
                                        Select::make('title')
                                            ->options([
                                                'Mr.' => 'Mr.',
                                                'Mrs.' => 'Mrs.',
                                                'Ms.' => 'Ms.',
                                                'Dr.' => 'Dr.',
                                            ]),
                                        TextInput::make('first_name')->required(),
                                        TextInput::make('last_name')->required(),
                                        TextInput::make('email')->email()->required(),
                                        TextInput::make('phone'),
                                    ])->columns(2),

                                Section::make('Address')
                                    ->schema([
                                        Repeater::make('addresses')
                                            ->relationship()
                                            ->schema([
                                                TextInput::make('street')->required(),
                                                TextInput::make('city')->required(),
                                                TextInput::make('state')->required(),
                                                TextInput::make('zip')->required(),
                                            ])
                                            ->minItems(1)
                                            ->maxItems(1),
                                    ]),
                            ]),
                    ]),

                Section::make('Tax Information')
                    ->schema([
                        Select::make('filing_status')
                            ->required()
                            ->options(FilingStatus::class),
                        TextInput::make('filing_year')
                            ->required()
                            ->numeric()
                            ->default(date('Y')),
                    ])
                    ->columns(2),

                Section::make('Donation Details')
                    ->schema([
                        TextInput::make('amount')
                            ->label('Amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->formatStateUsing(fn ($state) => $state ? number_format($state / 100, 2, '.', '') : null)
                            ->dehydrateStateUsing(fn ($state) => (int) ((float) $state * 100)),
                        Select::make('payment_method')
                            ->options(PaymentMethod::class)
                            ->default(PaymentMethod::Check->value)
                            ->live()
                            ->required(),
                        TextInput::make('check_number')
                            ->visible(fn ($get) => in_array($get('payment_method'), [PaymentMethod::Check, PaymentMethod::Check->value])),
                        Select::make('school_id')
                            ->relationship('school', 'name')
                            ->searchable()
                            ->columnSpanFull(),
                        Select::make('status')
                            ->options(fn (string $operation) => $operation === 'create'
                                ? [
                                    DonationStatus::Pending->value => DonationStatus::Pending->getLabel(),
                                    DonationStatus::Paid->value => DonationStatus::Paid->getLabel(),
                                ]
                                : DonationStatus::class)
                            ->default(DonationStatus::Pending)
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Tax Professional')
                    ->schema([
                        TextInput::make('tax_professional_name')
                            ->maxLength(255),
                        TextInput::make('tax_professional_phone')
                            ->tel()
                            ->maxLength(50),
                        TextInput::make('tax_professional_email')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(3)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('school.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('donor.last_name')
                    ->label('Donor')
                    ->formatStateUsing(fn (Donation $record) => $record->donor_name)
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('donor.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('USD', divideBy: 100)
                    ->sortable(),
                TextColumn::make('filing_year')
                    ->badge()
                    ->sortable(),
                TextColumn::make('filing_status')
                    ->badge()
                    ->color(fn (FilingStatus $state): string => match ($state) {
                        FilingStatus::Single => 'info',
                        FilingStatus::MarriedFilingJointly => 'success',
                        FilingStatus::MarriedFilingSeparately => 'warning',
                    })
                    ->formatStateUsing(fn (FilingStatus $state): string => match ($state) {
                        FilingStatus::Single => 'Single',
                        FilingStatus::MarriedFilingJointly => 'MFJ',
                        FilingStatus::MarriedFilingSeparately => 'MFS',
                    }),
                TextColumn::make('receipt_sent_at')
                    ->label('Receipt Sent')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('school')
                    ->relationship('school', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('filing_year')
                    ->options(fn () => Donation::query()
                        ->selectRaw('DISTINCT filing_year')
                        ->orderByDesc('filing_year')
                        ->pluck('filing_year', 'filing_year')
                        ->toArray()
                    ),
                SelectFilter::make('filing_status')
                    ->options([
                        'single' => 'Single',
                        'married_filing_jointly' => 'Married Filing Jointly',
                        'married_filing_separately' => 'Married Filing Separately',
                    ]),
                Filter::make('receipt_pending')
                    ->query(fn (Builder $query): Builder => $query->whereNull('receipt_sent_at'))
                    ->label('Receipt Pending'),
            ])
            ->actions([
                Action::make('viewReceipt')
                    ->label('View Receipt')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->action(function (Donation $record) {
                        $pdfContent = Pdf::view('pdf.receipt', ['donation' => $record->load(['school', 'transactions'])])
                            ->format('letter')
                            ->base64();

                        return response()->streamDownload(function () use ($pdfContent) {
                            echo base64_decode($pdfContent);
                        }, 'donation-receipt-'.$record->id.'.pdf');
                    }),
                Action::make('emailReceipt')
                    ->label('Email Receipt')
                    ->icon('heroicon-o-envelope')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Send Receipt Email')
                    ->modalDescription(function (Donation $record) {
                        /** @var Donor $donor */
                        $donor = $record->donor;

                        return "Send receipt email to {$donor->email}?";
                    })
                    ->modalSubmitActionLabel('Send Email')
                    ->action(function (Donation $record) {
                        /** @var Donor $donor */
                        $donor = $record->donor;

                        Mail::to($donor->email)->send(new DonationReceipt($record));

                        $record->update(['receipt_sent_at' => now()]);

                        Notification::make()
                            ->title('Receipt Sent')
                            ->body("Receipt email sent to {$donor->email}")
                            ->success()
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListDonations::route('/'),
            'create' => Pages\CreateDonation::route('/create'),
            'view' => Pages\ViewDonation::route('/{record}'),
            'edit' => Pages\EditDonation::route('/{record}/edit'),
        ];
    }

    /** @return Builder<Donation> */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('donor')) {
            $query->whereHas('donor', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        return $query;
    }
}
