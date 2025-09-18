<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'remind_at',
        'event_at',
        'status',
        'created_by'
    ];

    protected $visible = [
        'id',
        'title',
        'description',
        'remind_at',
        'event_at',
    ];

    protected $casts = [
        'remind_at' => 'string',
        'event_at'  => 'string',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
