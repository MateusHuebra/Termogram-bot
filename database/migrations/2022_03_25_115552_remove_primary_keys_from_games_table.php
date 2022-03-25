<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePrimaryKeysFromGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['word_date']);
            $table->dropIndex('games_word_date_foreign');
            $table->dropPrimary(['user_id', 'word_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('word_date')->references('date')->on('words');

            $table->primary(['user_id', 'word_date']);
        });
    }
}
