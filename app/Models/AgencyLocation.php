<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgencyLocation extends Model
{
    protected $fillable = [
        'agency_id',
        'lat',
        'lng',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }
}
