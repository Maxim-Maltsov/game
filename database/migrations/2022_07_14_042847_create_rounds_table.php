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
            
            $table->id(); // Сделать поле значением bigInt и передавать из Vue JS как значение.

            $table->foreignId('game_id')
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->boolean('status')->default(false)->comment('is boolean value: 0 - NO_FINISHED, 1 - FINISHED');
            
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

            $table->boolean('draw')->default(false)->comment('is boolean value: 0 - NO, 1 - YES');

            $table->timestamps();
        });

         /* Поле 'round_id' из таблице 'moves' со ссылкой на таблицу 'round' перенесено в миграцию create_rounds_table,
             так как возникает ошибка при запуске миграций, которые ссылаются друг на друга 
             Миграция не может быть создана, так как в ней существует ссылка на таблицу которой ещё нет.
             Если изменять порядок запуска миграций, то происходит обратная ситуации. 
             
            На данный момен закоментировано, так как это решение вызывает ошибку при удалении миграций. 
            Cоздано простое поле без ссылки $table->bigInteger('round_id') в миграции create_moves_table.
        */

        // Schema::table('moves', function (Blueprint $table) {
            
        //     $table->foreignId('round_id')
        //           ->constrained()
        //           ->cascadeOnUpdate()
        //           ->cascadeOnDelete();
        // });
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
