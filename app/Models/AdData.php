<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdData extends Model
{
    use HasFactory;
    
    public function getStatTimeDayAttribute()
    {
        return date('Y-m-d',strtotime($this->attributes['stat_time_day']));
    }
}
