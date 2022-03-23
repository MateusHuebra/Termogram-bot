<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttempsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attemps', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->date('word_date');
            $table->unsignedTinyInteger('number');
            $table->string('word', 5);

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('word_date')->references('date')->on('words');

            $table->primary(['user_id', 'word_date', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attemps');
    }
}
