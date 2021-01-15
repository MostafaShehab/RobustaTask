<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeatsStopTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seats_stop', function (Blueprint $table) {
            $table->id();
            $table->integer('seat_id');
            $table->integer('stop_id');
            $table->boolean('is_booked');
            $table->integer('booking_user_id');
            $table->foreign('seat_id')->references('id')->on('seats');
            $table->foreign('stop_id')->references('id')->on('stops');
            $table->foreign('booking_user_id')->references('id')->on('users');
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
        Schema::dropIfExists('seats_stop');
    }
}
