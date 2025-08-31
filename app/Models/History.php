<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasUuids;
    protected $fillable = [
        'role',
        'user_id',
        'parts',
    ];
    // fillable or guarded
    // protected $guarded = [];

    protected $casts = [
        'parts' => 'array'
    ];
}
