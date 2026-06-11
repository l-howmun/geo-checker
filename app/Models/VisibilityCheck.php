<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisibilityCheck extends Model
{
    protected $fillable = [
        'brand',
        'prompt',
        'engine',
        'mentioned',
        'snippet',
        'sentiment',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'mentioned' => 'boolean',
        ];
    }
}
