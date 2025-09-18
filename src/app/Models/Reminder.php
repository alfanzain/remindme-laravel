<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'remind_at',
        'event_at',
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
}
