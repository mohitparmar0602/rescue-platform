<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgencyDocument extends Model
{
    protected $fillable = [
        'agency_id',
        'file_path',
        'document_type',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }
}
