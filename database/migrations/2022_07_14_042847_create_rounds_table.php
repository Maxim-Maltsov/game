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
        Schema::create('rounds', function (Blueprint $table) {
            
            $table->id();

            $table->foreignId('game_id')
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            
            $table->bigInteger('move_player_1')->unsigned()->nullable()->comment('is move_id');
            $table->index(['move_player_1']);
            $table->foreign('move_player_1')
                  ->references('id')
                  ->on('moves')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->bigInteger('move_player_2')->unsigned()->nullable()->comment('is move_id');
            $table->index(['move_player_2']);
            $table->foreign('move_player_2')
                  ->references('id')
                  ->on('moves')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            
            $table->bigInteger('winned_player')->unsigned()->nullable()->comment('is user_id');
            $table->index(['winned_player']);
            $table->foreign('winned_player')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->boolean('draw')->default(false)->comment('is boolean value: 1 - YES, 0 - NO');
            $table->boolean('finish')->default(false)->comment('is boolean value: 1 - YES, 0 - NO');

            $table->timestamps();
        });

        
        Schema::table('moves', function (Blueprint $table) {
            
            $table->foreignId('round_id')
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rounds');
    }
};
