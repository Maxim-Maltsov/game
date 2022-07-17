<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('moves', function (Blueprint $table) {
            
            $table->id();

            $table->foreignId('game_id')
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->bigInteger('round_number')->unsigned()->comment('is number in "rounds table"');
           
            $table->bigInteger('player_id')->unsigned()->comment('is user_id');
            $table->index(['player_id']);
            $table->foreign('player_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->integer('figure')->unsigned()->default(0)
                  ->comment('NONE = 0; ROCK = 1; SCISSORS = 2;
                             PAPER = 3; LIZARD = 4; SPOCK = 5;');

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
        Schema::dropIfExists('moves');
    }
};
