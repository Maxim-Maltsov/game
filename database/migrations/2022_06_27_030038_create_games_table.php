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
        Schema::create('games', function (Blueprint $table) {

            $table->id();

            $table->bigInteger('player_1')->unsigned()->comment('is user_id');
            $table->index(['player_1']);
            $table->foreign('player_1')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->bigInteger('player_2')->unsigned()->comment('is user_id');
            $table->index(['player_2']);
            $table->foreign('player_2')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->integer('status')->default(0)->comment('WAITING_PLAYER = 0; IN_PROCESS = 1; FINISHED = 2;');
            $table->timestamp('start')->nullable()->comment('is start game');
            $table->timestamp('end')->nullable()->comment('is end game');
            
            $table->bigInteger('winned_player')->unsigned()->nullable()->comment('is user_id');
            $table->index(['winned_player']);
            $table->foreign('winned_player')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->bigInteger('leaving_player')->unsigned()->nullable()->comment('is user_id');
            $table->index(['leaving_player']);
            $table->foreign('leaving_player')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->boolean('need_start_new_round')->default(false)->comment('is boolean value: 0 - NO, 1 - YES');

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
        Schema::dropIfExists('games');
    }
};
