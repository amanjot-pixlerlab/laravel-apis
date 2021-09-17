<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Advertisers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id')->unique();
            $table->string('advertiser_name');
            $table->string('image')->nullable();
            $table->text('access_token')->nullable();
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advertisers');
    }
}