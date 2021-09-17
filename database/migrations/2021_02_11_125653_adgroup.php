<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Adgroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adgroups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('adgroup_id')->unique();
            $table->string('adgroup_name')->nullable();
            $table->float('total_cpc')->nullable();
            $table->float('total_ctr')->nullable();
            $table->float('total_spend')->nullable();
            $table->float('total_cpm')->nullable();
            $table->integer('total_impressions')->nullable();
            $table->integer('total_clicks')->nullable();
            $table->integer('total_reach')->nullable();
            $table->dateTime('date')->nullable();
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
        Schema::dropIfExists('adgroups');
    }
}
