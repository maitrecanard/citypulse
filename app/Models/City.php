<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * City model representing a commune in the CityPulse system.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $address
 * @property string $postal_code
 * @property string $department
 * @property string $region
 * @property int|null $population
 * @property int|null $mayor_id
 * @property string $subscription_status
 * @property string|null $stripe_subscription_id
 */
class City extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'address',
        'postal_code',
        'department',
        'region',
        'population',
        'mayor_id',
        'subscription_status',
        'stripe_subscription_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'id',
        'stripe_subscription_id',
    ];

    /**
     * Get the mayor of this city.
     */
    public function mayor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mayor_id');
    }

    /**
     * Get all users belonging to this city.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all doleances for this city.
     */
    public function doleances(): HasMany
    {
        return $this->hasMany(Doleance::class);
    }

    /**
     * Get all events for this city.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get all announcements for this city.
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    /**
     * Get all alerts for this city.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    /**
     * Get all interventions for this city.
     */
    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class);
    }

    /**
     * Get all services for this city.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get all vehicles for this city.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Check if the city has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return in_array($this->subscription_status, ['active', 'trial']);
    }
}
