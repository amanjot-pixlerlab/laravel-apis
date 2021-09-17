<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\CronsController;
class UpdateTiktokAdsUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktokadsurl:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tiktok Ads URL Update';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $cronsController = new CronsController();
        $cronsController->updateCron();
        $cronsController->updateVideosForTiktokItems();
        return 0;
    }
}
