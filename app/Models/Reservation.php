<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'user_id',
        'ball_type_id',
        'pitch_type_id',
        'racket_type_id',
        'number_of_balls',
        'number_of_rackets',
        'start_time',
        'end_time',
        'total_price',
        'status',
    ];

    protected $casts = [
        'start_time'  => 'datetime',
        'end_time'    => 'datetime',
        'total_price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pitchType(): BelongsTo
    {
        return $this->belongsTo(ItemType::class, 'pitch_type_id');
    }

    public function ballType(): BelongsTo
    {
        return $this->belongsTo(ItemType::class, 'ball_type_id');
    }

    public function racketType(): BelongsTo
    {
        return $this->belongsTo(ItemType::class, 'racket_type_id');
    }
}
