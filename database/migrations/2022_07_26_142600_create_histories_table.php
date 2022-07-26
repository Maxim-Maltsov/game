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
        Schema::create('histories', function (Blueprint $table) {
            
            $table->id();

            $table->foreignId('game_id')
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->bigInteger('round_number')->unsigned()->comment('is number in "rounds table"');

            $table->integer('move_player_1')->unsigned()->default(0)
                  ->comment('NONE = 0; ROCK = 1; SCISSORS = 2;
                             PAPER = 3; LIZARD = 4; SPOCK = 5;');

            $table->integer('move_player_2')->unsigned()->default(0)
                  ->comment('NONE = 0; ROCK = 1; SCISSORS = 2;
                             PAPER = 3; LIZARD = 4; SPOCK = 5;');

            $table->bigInteger('winned_player')->unsigned()->nullable()->comment('is user_id');
            $table->index(['winned_player']);
            $table->foreign('winned_player')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->boolean('draw')->default(false)->comment('is boolean value: 0 - NO, 1 - YES');
            $table->boolean('timeout')->default(false)->comment('is boolean value: 0 - NO, 1 - YES');

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
        Schema::dropIfExists('histories');
    }
};
