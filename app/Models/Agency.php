<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Agency extends Model
{
    protected $fillable = [
        'name',
        'type',
        'registration_no',
        'status',
        'lat',
        'lng',
        'address',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(AgencyDocument::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function alerts(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Alert::class, 'alert_agency');
    }

    /** Messages received by this agency */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_agency_id');
    }
}
