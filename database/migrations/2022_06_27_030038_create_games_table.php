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

            $table->bigInteger('player_1')->unsigned();
            $table->bigInteger('player_2')->unsigned();

            $table->integer('status')->default(0);
            $table->timestamp('start')->nullable();
            $table->timestamp('end')->nullable();
            

            $table->bigInteger('winned_player')->unsigned()->nullable();
            $table->bigInteger('leaving_player')->unsigned()->nullable();

            $table->index(['player_1']);
            $table->foreign('player_1')
                  ->references('id')->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->index(['player_2']);
            $table->foreign('player_2')
                  ->references('id')->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->index(['winned_player']);
            $table->foreign('winned_player')
                  ->references('id')->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->index(['leaving_player']);
            $table->foreign('leaving_player')
                  ->references('id')->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

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
