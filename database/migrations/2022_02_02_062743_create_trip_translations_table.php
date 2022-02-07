<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trip_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id')->unsigned();
            $table->string('locale')->index();
            $table->string('name');
            $table->unique(['trip_id', 'locale']);
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trip_translations');
    }
}
