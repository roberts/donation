<?php

namespace App\Models;

use App\Enums\SchoolType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Roberts\Support\Traits\HasCreator;
use Roberts\Support\Traits\HasUpdater;

/**
 * @property int $id
 * @property int|null $ibe_id
 * @property string $name
 * @property SchoolType $type
 * @property int|null $creator_id
 * @property int|null $updater_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read Collection<int, Donation> $donations
 * @property-read Collection<int, Note> $notes
 */
class School extends Model
{
    use HasCreator;

    /** @use HasFactory<\Database\Factories\SchoolFactory> */
    use HasFactory;

    use HasUpdater;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ibe_id',
        'name',
        'type',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => SchoolType::class,
        ];
    }

    /**
     * Get the donations for this school.
     *
     * @return HasMany<Donation, $this>
     */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * @return MorphMany<Note, $this>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }
}
