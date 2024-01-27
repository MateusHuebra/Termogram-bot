<?php

namespace App\Models;

use App\Services\ServerLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function scopeToday($query) {
        $date = date('Y-m-d');
        return $query->where('date', $date);
    }

    public function scopeLastTimeUsed($query, $word) {
        return $query->where('value', $word)
            ->orderBy('date', 'desc');
    }

    public function scopeLast($query) {
        return $query->orderBy('date', 'desc');
    }

}
