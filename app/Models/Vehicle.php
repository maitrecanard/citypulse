<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Vehicle model for city fleet management.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $type
 * @property string $plate_number
 * @property string|null $team
 * @property string $status
 * @property \Carbon\Carbon|null $next_maintenance_at
 * @property int $city_id
 */
class Vehicle extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'plate_number',
        'team',
        'status',
        'next_maintenance_at',
        'city_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'id',
        'city_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'next_maintenance_at' => 'date',
        ];
    }

    /**
     * Get the city this vehicle belongs to.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the maintenance records for this vehicle.
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(VehicleMaintenance::class);
    }

    /**
     * Get the interventions using this vehicle.
     */
    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class);
    }
}
