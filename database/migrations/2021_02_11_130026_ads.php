<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Ads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_id')->unique();
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->unsignedBigInteger('adgroup_id')->nullable();
            $table->string('ad_name')->nullable();
            $table->float('total_cpc')->nullable();
            $table->float('total_ctr')->nullable();
            $table->float('total_spend')->nullable();
            $table->float('total_cpm')->nullable();
            $table->integer('total_impressions')->nullable();
            $table->integer('total_clicks')->nullable();
            $table->integer('total_reach')->nullable();
            $table->enum('image_mode', ['IMAGE_MODE_GIF', 'IMAGE_MODE_GROUP', 'IMAGE_MODE_LARGE', 'IMAGE_MODE_LARGE_VERTICAL', 'IMAGE_MODE_SMALL', 'IMAGE_MODE_BIG_ONE', 'IMAGE_MODE_VIDEO', 'IMAGE_MODE_VIDEO_SQUARE', 'IMAGE_MODE_VIDEO_VERTICAL', 'MULTI_SQUARE_PICTURES', 'MULTI_RECTANGLE_PICTURES'])->nullable();
            $table->enum('opt_status', ['ENABLE', 'DISABLE'])->nullable();
            $table->text('ad_text')->nullable();
            $table->text('image_ids')->nullable();
            $table->text('playable_url')->nullable();
            $table->text('ad_texts')->nullable();
            $table->string('profile_image')->nullable();
            $table->text('landing_page_urls')->nullable();
            $table->string('call_to_action_id')->nullable();
            $table->text('landing_page_url')->nullable();
            $table->string('external_action')->nullable();
            $table->string('is_aco')->nullable();
            $table->boolean('is_creative_authorized')->nullable();
            $table->enum('status', ['AD_STATUS_CAMPAIGN_DELETE', 'AD_STATUS_ADGROUP_DELETE', 'AD_STATUS_DELETE', 'AD_STATUS_ADVERTISER_AUDIT_DENY', 'AD_STATUS_ADVERTISER_AUDIT', 'AD_STATUS_BALANCE_EXCEED', 'AD_STATUS_CAMPAIGN_EXCEED', 'AD_STATUS_BUDGET_EXCEED', 'AD_STATUS_AUDIT', 'AD_STATUS_REAUDIT', 'AD_STATUS_AUDIT_DENY', 'AD_STATUS_ADGROUP_AUDIT_DENY', 'AD_STATUS_NOT_START', 'AD_STATUS_DONE', 'AD_STATUS_CAMPAIGN_DISABLE', 'AD_STATUS_ADGROUP_DISABLE', 'AD_STATUS_DISABLE', 'AD_STATUS_DELIVERY_OK', 'AD_STATUS_ALL', 'AD_STATUS_NOT_DELETE'])->nullable();
            $table->unsignedBigInteger('tiktok_item_id')->nullable();
            $table->string('call_to_action')->nullable();
            $table->string('page_id')->nullable();
            $table->text('video_id')->nullable();
            $table->string('display_name')->nullable();
            $table->string('open_url_type')->nullable();
            $table->text('open_url')->nullable();
            $table->text('impression_tracking_url')->nullable();
            $table->string('vast_moat')->nullable();
            $table->text('pixel_id')->nullable();
            $table->text('click_tracking_url')->nullable();
            $table->boolean('is_new_structure')->nullable();
            $table->string('app_name')->nullable();
            $table->dateTime('date')->nullable();
            $table->dateTime('create_time')->nullable();
            $table->dateTime('modify_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ads');
    }
}
