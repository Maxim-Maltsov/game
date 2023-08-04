<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Changes the online status of users to "offline" after being idle for a certain amount of time.
 */
class UpdateUsersOnlineStatus extends Command
{   
    /**
     *  UpdateUsersOnlineStatus command constructor.
     */
    public function __construct(private UserService $userService)
    {
        parent::__construct();
    }

    /**
    * Specifies the time interval after which the user receives a status change from "online" to "offline".
    */
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
        
        $this->userService->updateUserList();

        return 0;
    }
}
