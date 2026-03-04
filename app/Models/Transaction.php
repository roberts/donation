<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Roberts\Support\Traits\HasCreator;
use Roberts\Support\Traits\HasUpdater;

/**
 * @property int $id
 * @property int|null $donation_id
 * @property string $payment_intent_id
 * @property int $amount
 * @property TransactionStatus $status
 * @property bool $livemode
 * @property array<string, mixed>|null $payload
 * @property int|null $creator_id
 * @property int|null $updater_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read Donation|null $donation
 * @property-read Collection<int, Note> $notes
 */
class Transaction extends Model
{
    use HasCreator;

    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;

    use HasUpdater;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'donation_id',
        'payment_intent_id',
        'amount',
        'status',
        'livemode',
        'payload',
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
            'status' => TransactionStatus::class,
            'livemode' => 'boolean',
            'payload' => 'array',
        ];
    }

    public function getAmountDollarsAttribute(): string
    {
        return number_format($this->amount / 100, 2);
    }

    /**
     * Get the donation associated with this transaction.
     *
     * @return BelongsTo<Donation, $this>
     */
    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }

    /**
     * @return MorphMany<Note, $this>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Determine if the transaction succeeded.
     */
    public function isSuccessful(): bool
    {
        return $this->status->isSuccessful();
    }
}
