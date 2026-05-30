<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'container_id',
        'location',
        'timestamp',
        'description',
    ];

    /**
     * Get the container that owns the tracking log.
     * Relasi: TrackingLog belongs to one Container.
     */
    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }
}
