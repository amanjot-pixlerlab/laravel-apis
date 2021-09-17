<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;
    protected $casts = ['ad_id' => 'string', 'campaign_id' => 'string', 'adgroup_id'];

    public function getTotalCtrAttribute()
    {
        $total_ctr = ($this->attributes['total_ctr']==null) ? "N/A" : number_format($this->attributes['total_ctr'],2);
        return  $total_ctr;
    }

    public function getTotalImpressionsAttribute()
    {
        $total_impressions = ($this->attributes['total_impressions']==null) ? "N/A" : $this->attributes['total_impressions'];
        return  $total_impressions;
    }

    public function getTotalReachAttribute()
    {
        $total_reach = ($this->attributes['total_reach']==null) ? "N/A" : $this->attributes['total_reach'];
        return  $total_reach;
    }

    public function getTotalClicksAttribute()
    {
        $total_clicks = ($this->attributes['total_clicks']==null) ? "N/A" : $this->attributes['total_clicks'];
        return  $total_clicks;
    }
}
