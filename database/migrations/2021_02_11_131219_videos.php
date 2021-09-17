<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Videos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_id')->unique();
            $table->unsignedBigInteger('material_id')->nullable();
            $table->text('url')->nullable();
            $table->boolean('displayable')->nullable();
            $table->string('duration')->nullable();
            $table->text('allowed_placements')->nullable();
            $table->text('poster_url')->nullable();
            $table->string('signature')->nullable();
            $table->boolean('allow_download')->nullable();
            $table->integer('bit_rate')->nullable();
            $table->string('size')->nullable();
            $table->string('format')->nullable();
            $table->float('height')->nullable();
            $table->float('width')->nullable();
            $table->string('file_name')->nullable();
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
        Schema::dropIfExists('videos');
    }
}
