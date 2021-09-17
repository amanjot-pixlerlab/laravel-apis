<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use App\Models\Review;
class Campaign extends Model
{
    use HasFactory;

    /**
     * Get the comments for the blog post.
     */

    protected $fillable = ['custom_total_spend', 'custom_total_cpm'];
    protected $casts = ['advertiser_id' => 'string', 'campaign_id' => 'string'];

    public function campaigndata()
    {
        return $this->hasMany(CampaignData::class, 'campaign_id', 'campaign_id');
    }

    /**
     * Get the reviews for the blog post.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'campaign_id', 'campaign_id'); 
    }
      
}
