<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function scopeOfId($query, $userId) {
        return $query->where('id', $userId);
    }
    
    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

}
