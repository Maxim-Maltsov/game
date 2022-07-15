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


            $table->bigInteger('round_id')->unsigned()->nullable();
            
            /* Поле  $table->foreignId('round_id')
                           ->constrained()
                           ->cascadeOnUpdate()
                           ->cascadeOnDelete();

                со ссылкой на таблицу 'round' перенесено в миграцию create_rounds_table,
                так как возникает ошибка при запуске миграций, которые ссылаются друг на друга 
                Миграция не может быть создана, так как в ней существует ссылка на таблицу которой ещё нет.
                Если изменять порядок запуска миграций, то происходит обратная ситуации. 
                
                Для решения проблемы можно также создать простое поле  $table->bigInteger('round_id') ,
                но оно не будет являтся ссылкой на 'id' в таблице 'rounds'.
                Cоздано простое поле без ссылки $table->bigInteger('round_id') в миграции create_moves_table. 
                Так как это решение вызывает ошибку при удалении миграций.
             */

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
