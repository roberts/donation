<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Roberts\Support\Traits\HasCreator;
use Roberts\Support\Traits\HasUpdater;

/**
 * @property int $id
 * @property string $type
 * @property string $street
 * @property string|null $street_line_2
 * @property string $city
 * @property string $state
 * @property string $postal_code
 * @property string $country
 * @property int $addressable_id
 * @property string $addressable_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model|\Eloquent $addressable
 */
class Address extends Model
{
    use HasCreator;

    /** @use HasFactory<\Database\Factories\AddressFactory> */
    use HasFactory;

    use HasUpdater;
    use SoftDeletes;

    protected $fillable = [
        'type',
        'street',
        'street_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'addressable_id',
        'addressable_type',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }
}
