<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
 * @property string|null $title
 * @property string $first_name
 * @property string $last_name
 * @property string|null $spouse_title
 * @property string|null $spouse_first_name
 * @property string|null $spouse_last_name
 * @property string $email
 * @property string|null $phone
 * @property int|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Collection<int, Donation> $donations
 * @property-read Collection<int, Address> $addresses
 * @property-read Collection<int, Note> $notes
 */
class Donor extends Model
{
    use HasCreator;

    /** @use HasFactory<\Database\Factories\DonorFactory> */
    use HasFactory;

    use HasUpdater;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'first_name',
        'last_name',
        'spouse_title',
        'spouse_first_name',
        'spouse_last_name',
        'email',
        'phone',
        'user_id',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function formatPhone(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $value);

        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            return substr($digits, 1);
        }

        return $digits;
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => self::formatPhone($value),
        );
    }

    /**
     * @return HasMany<Donation, $this>
     */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * @return MorphMany<Address, $this>
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * @return MorphMany<Note, $this>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }
}
