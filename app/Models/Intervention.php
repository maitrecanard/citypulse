<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Intervention model for scheduled city maintenance and tasks.
 *
 * @property int $id
 * @property string $uuid
 * @property string $title
 * @property string $description
 * @property string $status
 * @property string $priority
 * @property \Carbon\Carbon $scheduled_at
 * @property \Carbon\Carbon|null $completed_at
 * @property int $city_id
 * @property int|null $assigned_to
 * @property int|null $vehicle_id
 * @property int $created_by
 * @property int|null $doleance_id
 */
class Intervention extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'scheduled_at',
        'completed_at',
        'city_id',
        'assigned_to',
        'vehicle_id',
        'created_by',
        'doleance_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'id',
        'city_id',
        'assigned_to',
        'vehicle_id',
        'created_by',
        'doleance_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the city this intervention belongs to.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the user assigned to this intervention.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the vehicle used for this intervention.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user who created this intervention.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the doleance linked to this intervention.
     */
    public function doleance(): BelongsTo
    {
        return $this->belongsTo(Doleance::class);
    }
}
