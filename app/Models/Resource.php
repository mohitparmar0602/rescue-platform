<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = [
        'agency_id',
        'type',
        'name',
        'quantity',
        'status',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }
}
