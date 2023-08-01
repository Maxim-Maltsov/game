<?php

namespace App\Console\Commands;

use App\Events\AmountUsersOnlineChangedEvent;
use App\Http\Resources\UserCollection;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateUsersOnlineStatus extends Command
{   
    /**
     *  UpdateUsersOnlineStatus command constructor.
     */
    public function __construct(private UserRepository $userRepository) {
        
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
        
        // Getting a list of "online" users and passing it through the "AmountUsersOnlineChangedEven" event to the client side for further rendering.                  
        $users = $this->userRepository->getEveryoneWhoOnlineWithPaginated(4);

        if ($users->isNotEmpty()) {
            AmountUsersOnlineChangedEvent::dispatch(UserCollection::make($users));
        }

        return 0;
    }
}
