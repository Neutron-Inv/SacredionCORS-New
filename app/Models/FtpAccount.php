<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FtpAccount extends Model
{
    protected $fillable = [
        'name',
        'host',
        'port',
        'username',
        'password',
        'passive',
        'root_path',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'passive' => 'boolean',
            'is_active' => 'boolean',
            'port' => 'integer',
        ];
    }

    public function stations(): HasMany
    {
        return $this->hasMany(CorsStation::class);
    }
}
