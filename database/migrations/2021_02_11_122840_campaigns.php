<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Campaigns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->unique();
            $table->unsignedBigInteger('advertiser_id');
            $table->string('campaign_name');
            $table->enum('budget_mode', ['BUDGET_MODE_DAY', 'BUDGET_MODE_TOTAL', 'BUDGET_MODE_INFINITE'])->nullable();
            $table->enum('opt_status', ['ENABLE', 'DISABLE'])->nullable();
            $table->enum('status', ['CAMPAIGN_STATUS_DELETE', 'CAMPAIGN_STATUS_ADVERTISER_AUDIT_DENY', 'CAMPAIGN_STATUS_ADVERTISER_AUDIT', 'CAMPAIGN_STATUS_BUDGET_EXCEED',
            'CAMPAIGN_STATUS_DISABLE', 'CAMPAIGN_STATUS_ENABLE', 'CAMPAIGN_STATUS_ALL', 'CAMPAIGN_STATUS_NOT_DELETE'])->nullable();
            $table->float('total_cpc')->nullable();
            $table->float('total_ctr')->nullable();
            $table->float('total_spend')->nullable();
            $table->float('total_cpm')->nullable();
            $table->integer('total_impressions')->nullable();
            $table->integer('total_clicks')->nullable();
            $table->integer('total_reach')->nullable();
            $table->decimal('custom_total_spend',20,2)->nullable();
            $table->decimal('custom_total_cpm',20,2)->nullable();
            $table->dateTime('create_time')->nullable();
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
        Schema::dropIfExists('campaigns');
    }
}
