<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserPackage;
use Carbon\Carbon;

class CheckPackageExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'packages:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change the status of expired packages to "expired" and update exhausted minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        // 1. Update packages that have passed their expiration date
        $expired = UserPackage::where('status', 'active')
            ->where('expires_at', '<', $now)
            ->update(['status' => 'expired']);

        // 2. Update packages that have consumed all their minutes
        $exhausted = UserPackage::where('status', 'active')
            ->where('remaining_minutes', '<=', 0)
            ->update(['status' => 'exhausted']);

        $this->info("Successfully processed: $expired expired packages and $exhausted exhausted packages.");
    }
}
