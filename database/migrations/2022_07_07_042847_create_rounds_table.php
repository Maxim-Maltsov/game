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

            $table->bigInteger('number')->unsigned()->comment('is round number');

            $table->integer('status')->default(0)->comment('0 - NO_FINISHED, 1 - FINISHED');
            
            $table->bigInteger('winned_player')->unsigned()->nullable()->comment('is user_id');
            $table->index(['winned_player']);
            $table->foreign('winned_player')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->boolean('draw')->default(false)->comment('is boolean value: 0 - NO, 1 - YES');

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
        Schema::dropIfExists('rounds');
    }
};
