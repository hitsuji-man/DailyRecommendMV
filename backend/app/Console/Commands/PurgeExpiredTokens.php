<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class PurgeExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:purge-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired personal access tokens';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = PersonalAccessToken::where(
            'created_at',
            '<',
            now()->subHours(24)
        )->delete();

        $this->info("Deleted {$count} expired tokens.");

        return 0;
    }
}
