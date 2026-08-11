<?php

namespace App\Models;

use Database\Factories\PasskeyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'challenge_expires_at',
    'counter',
    'credential_id',
    'credential_id_hash',
    'current_challenge',
    'device_id',
    'label',
    'last_used_at',
    'public_key',
    'registered_at',
    'revoked_at',
    'status',
    'transports',
    'user_handle',
    'user_id',
])]
#[Hidden([
    'credential_id',
    'credential_id_hash',
    'current_challenge',
    'public_key',
])]
class Passkey extends Model
{
    /** @use HasFactory<PasskeyFactory> */
    use HasFactory, SoftDeletes;

    public function authenticationEvents(): HasMany
    {
        return $this->hasMany(AuthenticationEvent::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
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
            'challenge_expires_at' => 'datetime',
            'counter' => 'integer',
            'credential_id' => 'encrypted',
            'current_challenge' => 'encrypted',
            'last_used_at' => 'datetime',
            'public_key' => 'encrypted',
            'registered_at' => 'datetime',
            'revoked_at' => 'datetime',
            'transports' => 'array',
        ];
    }
}
