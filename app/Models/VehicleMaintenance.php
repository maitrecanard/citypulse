<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * VehicleMaintenance model for tracking vehicle maintenance records.
 *
 * @property int $id
 * @property string $uuid
 * @property int $vehicle_id
 * @property string $description
 * @property string $type
 * @property float|null $cost
 * @property \Carbon\Carbon $performed_at
 * @property \Carbon\Carbon|null $next_due_at
 * @property string|null $performed_by
 */
class VehicleMaintenance extends Model
{
    use HasFactory, HasUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'vehicle_id',
        'description',
        'type',
        'cost',
        'performed_at',
        'next_due_at',
        'performed_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'id',
        'vehicle_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'performed_at' => 'date',
            'next_due_at' => 'date',
        ];
    }

    /**
     * Get the vehicle this maintenance record belongs to.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
