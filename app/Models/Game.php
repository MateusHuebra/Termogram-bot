<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function scopeByUser($query, $userId) {
        $date = date('Y-m-d');
        return $query->where('user_id', $userId)
            ->where('word_date', $date);
    }
    
}
