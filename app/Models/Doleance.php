<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Doleance (grievance) model for citizen complaints.
 *
 * @property int $id
 * @property string $uuid
 * @property string $title
 * @property string $description
 * @property string $category
 * @property string $priority
 * @property string $status
 * @property string|null $admin_response
 * @property int $user_id
 * @property int $city_id
 * @property \Carbon\Carbon|null $consulted_at
 * @property \Carbon\Carbon|null $resolved_at
 */
class Doleance extends Model
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
        'category',
        'priority',
        'status',
        'admin_response',
        'user_id',
        'city_id',
        'consulted_at',
        'resolved_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'id',
        'user_id',
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
            'consulted_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Get the user who created this doleance.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the city this doleance belongs to.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the intervention linked to this doleance.
     */
    public function intervention(): HasOne
    {
        return $this->hasOne(Intervention::class);
    }
}
