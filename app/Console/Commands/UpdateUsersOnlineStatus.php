<?php

namespace App\Console\Commands;

use App\Events\AmountUsersOnlineChangedEvent;
use App\Http\Resources\UserCollection;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateUsersOnlineStatus extends Command
{   
    const EXPIRY_TIME_ONLINE_IN_MINUTES = 5;
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
        DB::table('users')->whereRaw(DB::raw('TIMESTAMPDIFF(MINUTE, last_activity, NOW()) >= ' . $this::EXPIRY_TIME_ONLINE_IN_MINUTES))
                          ->chunkById(100, function ($users) {
                            
                            foreach ($users as $user) {

                                DB::table('users')
                                    ->where('id', $user->id)
                                    ->update(['online_status' => User::OFFLINE]);
                            }
                          });
                          

        $users = User::getOnlineUsersPaginate(4);

        AmountUsersOnlineChangedEvent::dispatch(new UserCollection($users));

        return 0;
    }
}
