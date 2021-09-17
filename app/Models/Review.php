<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;
    protected $casts = ['advertiser_id' => 'string', 'campaign_id' => 'string'];

    public function getCreatedAtAttribute()
    {
        return date('M d, Y',strtotime($this->attributes['created_at']));
    }
}
