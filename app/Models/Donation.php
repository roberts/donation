<?php

namespace App\Models;

use App\Enums\DonationStatus;
use App\Enums\FilingStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Roberts\Support\Traits\HasCreator;
use Roberts\Support\Traits\HasUpdater;

/**
 * @property int $id
 * @property int $school_id
 * @property int $donor_id
 * @property PaymentMethod $payment_method
 * @property string|null $check_number
 * @property int $amount
 * @property string $status
 * @property int $filing_year
 * @property FilingStatus $filing_status
 * @property string|null $qco
 * @property string|null $school_name_snapshot
 * @property string|null $tax_professional_name
 * @property string|null $tax_professional_phone
 * @property string|null $tax_professional_email
 * @property Carbon|null $receipt_sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Donor|null $donor
 * @property-read School|null $school
 * @property-read Collection<int, Transaction> $transactions
 * @property-read string $donor_name
 * @property-read string $amount_dollars
 * @property-read string $formatted_mailing_address
 * @property-read Collection<int, Note> $notes
 */
class Donation extends Model
{
    use HasCreator;

    /** @use HasFactory<\Database\Factories\DonationFactory> */
    use HasFactory;

    use HasUpdater;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'donor_id',
        'payment_method',
        'check_number',
        'amount',
        'status',
        'filing_year',
        'filing_status',
        'qco',
        'school_name_snapshot',
        'tax_professional_name',
        'tax_professional_phone',
        'tax_professional_email',
        'receipt_sent_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => DonationStatus::class,
            'payment_method' => PaymentMethod::class,
            'filing_year' => 'integer',
            'filing_status' => FilingStatus::class,
            'receipt_sent_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to only include paid donations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaid($query)
    {
        return $query->where('status', DonationStatus::Paid);
    }

    /**
     * Scope a query to only include donations for a specific filing year.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('filing_year', $year);
    }

    /**
     * Get the school that received this donation.
     *
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return BelongsTo<Donor, $this>
     */
    public function donor(): BelongsTo
    {
        return $this->belongsTo(Donor::class);
    }

    /**
     * @return MorphMany<Address, $this>
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get the transactions for this donation.
     *
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return MorphMany<Note, $this>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Get the donor's full name.
     */
    public function getDonorNameAttribute(): string
    {
        if (! $this->donor) {
            return 'Unknown Donor';
        }

        /** @var Donor $donor */
        $donor = $this->donor;

        $name = trim("{$donor->title} {$donor->first_name} {$donor->last_name}");

        if ($donor->spouse_first_name) {
            $name2 = trim("{$donor->spouse_title} {$donor->spouse_first_name} {$donor->spouse_last_name}");
            $name .= " & {$name2}";
        }

        return $name;
    }

    /**
     * Get the amount in dollars.
     */
    public function getAmountDollarsAttribute(): string
    {
        return number_format($this->amount / 100, 2);
    }

    /**
     * Get formatted mailing address.
     */
    public function getFormattedMailingAddressAttribute(): string
    {
        if (! $this->donor) {
            return '';
        }

        /** @var Donor $donor */
        $donor = $this->donor;

        $address = $donor->addresses()->where('type', 'mailing')->first();

        if (! $address) {
            return '';
        }

        /** @var Address $address */
        return collect([
            $address->street,
            $address->street_line_2,
            "{$address->city}, {$address->state} {$address->postal_code}",
            $address->country !== 'US' ? $address->country : null,
        ])->filter()->implode("\n");
    }
}
