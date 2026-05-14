<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Alert extends Model
{
    protected $fillable = [
        'title',
        'description',
        'severity',
        'lat',
        'lng',
        'issued_by',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'lat'       => 'float',
        'lng'       => 'float',
    ];

    /** The admin who issued this alert */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /** Agencies that received this alert */
    public function agencies(): BelongsToMany
    {
        return $this->belongsToMany(Agency::class, 'alert_agency');
    }
}
