<?php

namespace App\Console\Commands;

use App\Services\VideoService;
use Illuminate\Console\Command;

class SaveMixedDailyList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'videos:save-mixed-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save mixed daily video list (cron job)';

    /**
     * Execute the console command.
     */
    public function handle(VideoService $videoService): int
    {
        $videoService->saveMixedDailyList();

        $this->info('Mixed daily videos saved successfully.');

        return 0;
    }
}
