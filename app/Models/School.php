<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    public const STATUSES = ['pending', 'active'];

    protected $fillable = [
        'title',
        'address',
        'status',
    ];
}
