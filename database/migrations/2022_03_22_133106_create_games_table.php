<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->date('word_date');
            $table->unsignedTinyInteger('won_at')->nullable();
            $table->boolean('ended');
            $table->unsignedBigInteger('season_id');

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('word_date')->references('date')->on('words');
            $table->foreign('season_id')->references('id')->on('seasons');

            $table->primary(['user_id', 'word_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('games');
    }
}
