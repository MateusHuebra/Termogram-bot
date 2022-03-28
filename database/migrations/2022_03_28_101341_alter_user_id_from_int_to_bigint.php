<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserIdFromIntToBigint extends Migration
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
            $table->dropUnique(['user_id', 'word_date']);
        });
        Schema::dropIfExists('attempts');

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->change();
        });

        Schema::table('games', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
            $table->foreign('user_id')->references('id')->on('users');
            $table->unique(['user_id', 'word_date']);
        });
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('word_date');
            $table->unsignedTinyInteger('number');
            $table->string('word', 5);

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('word_date')->references('date')->on('words');

            $table->unique(['user_id', 'word_date', 'number']);
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
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id', 'word_date']);
        });
        Schema::dropIfExists('attempts');
        
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('id')->change();
        });

        Schema::table('games', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->change();
            $table->foreign('user_id')->references('id')->on('users');
            $table->unique(['user_id', 'word_date']);
        });
        Schema::create('attempts', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->date('word_date');
            $table->unsignedTinyInteger('number');
            $table->string('word', 5);

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('word_date')->references('date')->on('words');

            $table->primary(['user_id', 'word_date', 'number']);
        });
    }
}
