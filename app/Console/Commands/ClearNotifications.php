<?php

namespace App\Console\Commands;

use App\Models\UserNotification;
use Illuminate\Console\Command;

class ClearNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete notifications older than five days';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        UserNotification::where('created_at', '<', now()->subDays(5))->delete();

        return Command::SUCCESS;
    }
}
