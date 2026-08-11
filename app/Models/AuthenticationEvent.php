<?php

namespace App\Models;

use Database\Factories\AuthenticationEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'device_id',
    'event',
    'ip_address',
    'metadata',
    'occurred_at',
    'passkey_id',
    'risk_level',
    'user_agent',
    'user_id',
])]
class AuthenticationEvent extends Model
{
    /** @use HasFactory<AuthenticationEventFactory> */
    use HasFactory;

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function passkey(): BelongsTo
    {
        return $this->belongsTo(Passkey::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }
}
