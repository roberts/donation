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
 * @property string $notable_type
 * @property int $notable_id
 * @property string $body
 * @property int|null $creator_id
 * @property int|null $updater_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read Model|\Eloquent $notable
 */
class Note extends Model
{
    use HasCreator;

    /** @use HasFactory<\Database\Factories\NoteFactory> */
    use HasFactory;

    use HasUpdater;
    use SoftDeletes;

    protected $fillable = [
        'notable_id',
        'notable_type',
        'body',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function notable(): MorphTo
    {
        return $this->morphTo();
    }
}
