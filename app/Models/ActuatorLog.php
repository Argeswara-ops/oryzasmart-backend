<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActuatorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'actuator_id',
        'user_id',
        'command',
        'mode',
        'source',
        'previous_status',
        'new_status',
    ];

    public function actuator(): BelongsTo
    {
        return $this->belongsTo(Actuator::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
