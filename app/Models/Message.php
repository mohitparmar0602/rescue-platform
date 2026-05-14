<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_agency_id',
        'body',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    // ── Encryption accessors ─────────────────────────────────────────────

    /** Store the body encrypted */
    public function setBodyAttribute(string $value): void
    {
        $this->attributes['body'] = Crypt::encryptString($value);
    }

    /** Read the body decrypted */
    public function getBodyAttribute(string $value): string
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception) {
            return '[unable to decrypt]';
        }
    }

    // ── Relationships ────────────────────────────────────────────────────

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiverAgency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'receiver_agency_id');
    }
}
