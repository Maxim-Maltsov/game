<?php

namespace App\Console\Commands;

use App\Events\AmountUsersOnlineChangedEvent;
use App\Http\Resources\UserCollection;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateUsersOnlineStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:update-online-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update users online status';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        
        $users = User::whereRaw(DB::raw("TIMESTAMPDIFF(MINUTE, last_activity, NOW()) >=" . env('EXPIRY_TIME_ONLINE_IN_MINUTES')))->get();

        
        if ($users->count() > 0) { 

            foreach ($users as $user) {

                $user->online_status = User::OFFLINE;
                $user->save();
            }

            AmountUsersOnlineChangedEvent::dispatch(new UserCollection($users));
        }

        
        return 0;
    }
}
