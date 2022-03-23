<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function scopeToday($query) {
        $date = date('Y-m-d');
        return $query->where('word_date', $date);
    }
    
}
