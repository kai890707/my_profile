<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the parent region (for hierarchical structure).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'parent_id');
    }

    /**
     * Get the child regions.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Region::class, 'parent_id');
    }
}
