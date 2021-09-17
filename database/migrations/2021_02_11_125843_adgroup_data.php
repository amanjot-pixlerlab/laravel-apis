<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdgroupData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adgroup_data', function (Blueprint $table) {
            $table->id();            
            $table->unsignedBigInteger('adgroup_id');
            $table->float('cpc')->nullable();
            $table->float('ctr')->nullable();
            $table->float('spend')->nullable();
            $table->float('cpm')->nullable();
            $table->integer('impressions')->nullable();
            $table->integer('clicks')->nullable();
            $table->integer('reach')->nullable();
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
        Schema::dropIfExists('adgroup_data');
    }
}
