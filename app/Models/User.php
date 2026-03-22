<?php

namespace App\Models;

use App\Traits\HasUuid;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User model for CityPulse.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $phone
 * @property string|null $address
 * @property string $role
 * @property int|null $city_id
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasUuid, HasApiTokens, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'address',
        'role',
        'city_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'id',
        'password',
        'remember_token',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the city this user belongs to.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the doleances created by this user.
     */
    public function doleances(): HasMany
    {
        return $this->hasMany(Doleance::class);
    }

    /**
     * Get the events created by this user.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    /**
     * Get the announcements created by this user.
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    /**
     * Get the alerts created by this user.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'created_by');
    }

    /**
     * Get the interventions assigned to this user.
     */
    public function assignedInterventions(): HasMany
    {
        return $this->hasMany(Intervention::class, 'assigned_to');
    }

    /**
     * Get the interventions created by this user.
     */
    public function createdInterventions(): HasMany
    {
        return $this->hasMany(Intervention::class, 'created_by');
    }

    /**
     * Check if user is an administre.
     */
    public function isAdministre(): bool
    {
        return $this->role === 'administre';
    }

    /**
     * Check if user is a maire.
     */
    public function isMaire(): bool
    {
        return $this->role === 'maire';
    }

    /**
     * Check if user is a secretaire.
     */
    public function isSecretaire(): bool
    {
        return $this->role === 'secretaire';
    }

    /**
     * Check if user is an agent.
     */
    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    /**
     * Check if user is staff (maire, secretaire, or agent).
     */
    public function isStaff(): bool
    {
        return in_array($this->role, ['maire', 'secretaire', 'agent']);
    }
}
