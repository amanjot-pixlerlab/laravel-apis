<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\CronsController;
class UpdateCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaign:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Campaign Update';

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
        $cronsController->updateAdvertisers();
        $cronsController->updateCampaign();
        $cronsController->updateCampaignDataForEachDay();
        $cronsController->updateAdGroupData();
        $cronsController->updateAdsData();
        $cronsController->updateAdsDataForEachDay();
        $cronsController->updateVideosData();
        $cronsController->updateTikTokAuthCode();

        return 0;
    }
}
