<?php

namespace App\Jobs;

use App\Http\Resources\GameResource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GameProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $game;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(GameResource $game)
    {
        $this->game = $game;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        while (true) {
        
            if ( true) {
                
                // 1. Создать новый раунд.

                // 2. Запустить событые начала нового раунда. Передать игру.

            }

            sleep(1);
        }

    }
}
