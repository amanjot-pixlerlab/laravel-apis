<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ad_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_id');
            $table->float('cpc')->nullable();
            $table->float('ctr')->nullable();
            $table->float('spend')->nullable();
            $table->float('cpm')->nullable();
            $table->integer('impressions')->nullable();
            $table->integer('clicks')->nullable();
            $table->integer('reach')->nullable();
            $table->dateTime('stat_time_day')->nullable();
            $table->timestamps();
            $table->unique(["ad_id", "stat_time_day"], 'unique_ad');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ad_data');
    }
}
