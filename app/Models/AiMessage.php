<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessage extends Model
{
    protected $fillable = [
        'user_id',
        'role',
        'content',
        'action_type',
        'action_data',
        'is_action_executed',
    ];

    protected $casts = [
        'action_data' => 'array',
        'is_action_executed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
