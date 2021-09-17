<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use \PDF;
use Illuminate\Support\Facades\DB;
use App\Models\Advertiser;
use App\Models\Campaign;
use App\Models\CampaignData;
use App\Models\Adgroup;
use App\Models\Ad;
use App\Models\AdData;
use App\Models\Video;
use App\Models\Cron;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\Mailer;
use Exception;

class CronsController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /* This function is for test purpose */
    public function campaignTest()
    {
        
        // $this->updateCron();
        // $this->updateAdvertisers();
        // $this->updateCampaign();
        // $this->updateCampaignDataForEachDay();
        // $this->updateAdGroupData();
        // $this->updateAdsData();
        // $this->updateAdsDataForEachDay();
        // $this->updateVideosData();
        // $this->updateTikTokAuthCode();
        // $this->updateVideosForTiktokItems();
    }

    public function updateCron()
    {
        $cron = Cron::find(1);
        $cron->touch();
    }

    /* This is common function to get data using curl from API's */
    public function curlGetExecution($method, $params, $page_size = 10, $page = 1, $result = [])
    {
        /* Get access token of advertiser */
        if (isset($params['advertiser_id']) && !empty($params['advertiser_id'])) {
            $advertiserId = $params['advertiser_id'];
            $advertiserList = $this->getDistinctAccessToken($advertiserId);
            if (count($advertiserList) > 0) {
                $access_token = $advertiserList[0]['access_token'];
            }
        }

        /* Get access token of advertiser */
        $params['page_size'] = $page_size;
        $params['page'] = $page;

        //$app_id = env('TIKTOK_API_ID');
        //$secret = env('TIKTOK_SECRET_KEY');

        $queryString = http_build_query($params);

        $url = env('TIKTOK_END_POINT') . $method . "/?" . $queryString;

        $authorization = "";
        
        try {
            // $url = $tiktokEndPoint . 'campaign/get/?advertiser_id=' . $advertiser_id
            if (isset($access_token)) {
                $authorization = "Access-Token: " . $access_token;
            }
            $cURLConnection = curl_init($url);
            curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($cURLConnection, CURLOPT_ENCODING, "");
            curl_setopt($cURLConnection, CURLOPT_MAXREDIRS, 10);
            curl_setopt($cURLConnection, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($cURLConnection, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($cURLConnection, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array($authorization));
            $apiResponse = curl_exec($cURLConnection);
            curl_close($cURLConnection);
            // $apiResponse - available data from the API request
            $jsonArrayResponse = json_decode($apiResponse);
            //var_dump($apiResponse);

       
        
        if($jsonArrayResponse->message == "OK")
        {
            //dd($jsonArrayResponse);
            if (isset($jsonArrayResponse->data) 
                && isset($jsonArrayResponse->data->list) 
                && count($jsonArrayResponse->data->list)
                && isset($jsonArrayResponse->data->page_info) 
                && $jsonArrayResponse->data->page_info->total_page >= $page && $jsonArrayResponse->data->page_info->total_page !=0
                ) 
            {

                //print_r($jsonArrayResponse);
                $page++;

                $result[]=$jsonArrayResponse;

                return $this->curlGetExecution($method, $params, $page_size, $page, $result);
            }
            else if($page==1)
            {
                $result=$jsonArrayResponse;
            }
            
        }
        else
        {
            $result = $jsonArrayResponse;
        }

        if(is_array($result) && count($result) > 0)
        {
            
            $result = $this->processedData($result);
            
        }
      
        return $result;
    } catch (Exception $e) {
        $result = (object)["message" => "", "code" => "", "data" => (object)["list"=>""]];
        return $result;
        //Mail::to(env('MAIL_TO'))->send(new Mailer($e));
    }

        
    }

    /* Process data when there are more than one page in the API's response */
    public function processedData($items)
    {
        //dd($items);
        $processedData = (object)["message"=>"OK", "code" => 0 , "data" => (object)["list"=>""]];
        $list = [];
        foreach($items as $item)
        {
            if(isset($item->data) && isset($item->data->list) && count($item->data->list) > 0)
            {
                $list = array_merge($list, $item->data->list);
            }
        }
        $processedData->data->list = $list;
        return $processedData;
    }

    //////////////////// Start Advertisers ////////////////////////

    /* This function used to get distinct access token of Advertisers */
    public function getDistinctAccessToken($advertiserId = null)
    {

        if (isset($advertiserId)) {
            return Advertiser::distinct()->select('access_token')->where('advertiser_id', $advertiserId)->get()->toArray();
        }
        return Advertiser::distinct()->select('access_token')->get()->toArray();
    }

    /* This function is used to insert and update advertisers data */
    public function updateAdvertisers()
    {
        $distinctAdvertiserList = $this->getDistinctAccessToken();

        if (count($distinctAdvertiserList) > 0) {
            foreach ($distinctAdvertiserList as $distinctAdvertiser) {
                $app_id = env('TIKTOK_API_ID');
                $secret = env('TIKTOK_SECRET_KEY');
                $access_token = $distinctAdvertiser['access_token'];
                $method = "oauth2/advertiser/get";
                $params = ['app_id' => $app_id, 'secret' => $secret, 'access_token' => $access_token];
                $advertisers = $this->curlGetExecution($method, $params);
                $this->saveAdvertisers($advertisers);
            }
        }
    }

    /* Save and Update advertisers */
    public function saveAdvertisers($advertisers)
    {

        if ($advertisers->message == "OK") {

            try {
                $advertiserData = [];
                if (isset($advertisers->data) && isset($advertisers->data->list) && count($advertisers->data->list)) {
                    foreach ($advertisers->data->list as $advertiser) {
                        $advertiserData[] = (array)$advertiser;
                    }
                }

                Advertiser::upsert($advertiserData, ['advertiser_id']);
            } catch (Exception $e) {
                //Mail::to(env('MAIL_TO'))->send(new Mailer($e));
            }
        }
    }

    //////////////////// End Advertisers ////////////////////////


    //////////////////// Start Campaign ////////////////////////

    /* This function is used to insert and update campaign data */
    public function updateCampaign()
    {
        // Get all advertisers list from database
        $advertisers = Advertiser::all()->pluck('advertiser_id')->toArray();

        foreach ($advertisers as $advertiserId) {
            // Update date from campaign api
            $this->updateGetCampaignData($advertiserId);


            // Update date from integrated campaign api
            $this->updateIntegratedCampaignData($advertiserId);
        }
    }


    /* This function gets data from  campaign/get/ and create or update campaign */
    public function updateGetCampaignData($advertiserId)
    {

        $method = "campaign/get/";
        $params = [
            'advertiser_id' => $advertiserId,
            'fields' => json_encode(['campaign_id', 'campaign_name', 'advertiser_id', 'opt_status', 'status', 'create_time', 'budget_mode'])
        ];

        $campaignData = [];

        $campaigns = $this->curlGetExecution($method, $params);
        $i = 0;

        if ($campaigns->message == "OK") {

            try {
                if (isset($campaigns->data) && isset($campaigns->data->list) && count($campaigns->data->list)) {
                    foreach ($campaigns->data->list as $campaign) {
                        $campaignData[$i]['advertiser_id'] = $campaign->advertiser_id;
                        $campaignData[$i]['campaign_id'] = $campaign->campaign_id;
                        $campaignData[$i]['campaign_name'] = $campaign->campaign_name;
                        $campaignData[$i]['budget_mode'] = $campaign->budget_mode;
                        $campaignData[$i]['opt_status'] = $campaign->opt_status;
                        $campaignData[$i]['status'] = $campaign->status;
                        $campaignData[$i]['create_time'] = $campaign->create_time;
                        $i++;
                    }
                }
                Campaign::upsert($campaignData, ['campaign_id', 'advertiser_id'], ['advertiser_id', 'campaign_id', 'campaign_name', 'budget_mode', 'opt_status', 'status', 'create_time']);
            } catch (Exception $e) {
                //Mail::to(env('MAIL_TO'))->send(new Mailer($e));
            }
        }

        return true;
    }

    /* This function is used to save and update data from intregarted API for campaigns */
    public function updateIntegratedCampaignData($advertiserId)
    {
        $method = "reports/integrated/get/";

        // Campaign data
        $params = [
            'advertiser_id' => $advertiserId,
            'report_type' => 'BASIC',
            'dimensions' => json_encode(['campaign_id']),
            'metrics' => json_encode(['campaign_name', 'spend', 'impressions', 'reach', 'stat_cost', 'cpc', 'cpm', 'show_cnt', 'click_cnt', 'ctr', 'show_uv']),
            'data_level' => 'AUCTION_CAMPAIGN',
            'lifetime' => true
        ];

        $campaigns = $this->curlGetExecution($method, $params);

        if ($campaigns->message == "OK") {
            try {
                $campaignData = [];
                $i = 0;
                if (isset($campaigns->data) && isset($campaigns->data->list) && count($campaigns->data->list)) {
                    foreach ($campaigns->data->list as $campaign) {
                        $campaignData[$i]['advertiser_id'] = $advertiserId;
                        $campaignData[$i]['campaign_id'] = $campaign->dimensions->campaign_id;
                        $campaignData[$i]['campaign_name'] = $campaign->metrics->campaign_name;
                        $campaignData[$i]['total_cpm'] = $campaign->metrics->cpm;
                        $campaignData[$i]['total_ctr'] = $campaign->metrics->ctr;
                        $campaignData[$i]['total_cpc'] = $campaign->metrics->cpc;
                        $campaignData[$i]['total_reach'] = $campaign->metrics->reach;
                        $campaignData[$i]['total_clicks'] = $campaign->metrics->clicks;
                        $campaignData[$i]['total_impressions'] = $campaign->metrics->impressions;
                        $campaignData[$i]['total_spend'] = $campaign->metrics->spend;
                        $i++;
                    }
                }
                Campaign::upsert($campaignData, ['campaign_id', 'advertiser_id'], ['campaign_id', 'advertiser_id', 'campaign_name', 'total_cpm', 'total_ctr', 'total_cpc', 'total_reach', 'total_clicks', 'total_impressions', 'total_spend']);
            } catch (Exception $e) {
                //Mail::to(env('MAIL_TO'))->send(new Mailer($e));
            }
        }
        return true;
    }

    /* This function is used to insert and update campaign data for each day */
    public function updateCampaignDataForEachDay()
    {

        // Get all campaigns list from database
        $campaigns = Campaign::select('campaign_id', 'advertiser_id', 'create_time')->get();

        foreach ($campaigns as $campaign) {
            $campaignId = $campaign->campaign_id;
            $advertiserId = $campaign->advertiser_id;
            $campaignCreateTime = $campaign->create_time;
            $campaignStartDate = date('Y-m-d', strtotime($campaignCreateTime));

            /* Check campaign data */
            $campaignData =  CampaignData::where('campaign_id', $campaignId)->orderBy('stat_time_day', 'DESC');

            if ($campaignData->count() > 0) {
                $campaignData  = $campaignData->latest()->first();
                $campaignStartDate = Carbon::parse($campaignData->stat_time_day)->format('Y-m-d');
            }

            /* Check campaign data */

            // Should not greater than today
            $now = Carbon::now()->format('Y-m-d');

            while (strtotime($campaignStartDate) <= strtotime($now)) {
                $dateAfterAddingDays = Carbon::parse($campaignStartDate)->addDays(20);
                $dateAfterAddingDays = $dateAfterAddingDays->format('Y-m-d');

                if (strtotime($dateAfterAddingDays)  >= strtotime($now)) {
                    $dateAfterAddingDays = $now;
                    $this->updateIntegratedCampaignDataForEachDay($advertiserId, $campaignId, $campaignStartDate, $dateAfterAddingDays);
                    break;
                }
                $this->updateIntegratedCampaignDataForEachDay($advertiserId, $campaignId, $campaignStartDate, $dateAfterAddingDays);

                $campaignStartDate = $dateAfterAddingDays;
            }
        }
    }

    /* This function is used to insert */

    public function updateIntegratedCampaignDataForEachDay($advertiserId, $campaignId, $startDate, $endDate)
    {
        $method = "reports/integrated/get/";

        // Campaign data
        $params = [
            'advertiser_id' => $advertiserId,
            'report_type' => 'BASIC',
            'dimensions' => json_encode(['campaign_id', 'stat_time_day']),
            'metrics' => json_encode(['campaign_name', 'spend', 'impressions', 'reach', 'stat_cost', 'cpc', 'cpm', 'show_cnt', 'click_cnt', 'ctr', 'show_uv']),
            'filters' => json_encode([['filter_value' => json_encode([$campaignId]), 'field_name' => 'campaign_ids', 'filter_type' => 'IN']]),
            'data_level' => 'AUCTION_CAMPAIGN',
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        $campaigns = $this->curlGetExecution($method, $params, 20);

        if ($campaigns->message == "OK") {
            $campaignData = [];
            $i = 0;

            try {
                if (isset($campaigns->data) && isset($campaigns->data->list) && count($campaigns->data->list)) {
                    foreach ($campaigns->data->list as $campaign) {
                        $campaignData[$i]['campaign_id'] = $campaign->dimensions->campaign_id;
                        $campaignData[$i]['cpm'] = $campaign->metrics->cpm;
                        $campaignData[$i]['ctr'] = $campaign->metrics->ctr;
                        $campaignData[$i]['cpc'] = $campaign->metrics->cpc;
                        $campaignData[$i]['reach'] = $campaign->metrics->reach;
                        $campaignData[$i]['clicks'] = $campaign->metrics->clicks;
                        $campaignData[$i]['impressions'] = $campaign->metrics->impressions;
                        $campaignData[$i]['spend'] = $campaign->metrics->spend;
                        $campaignData[$i]['stat_time_day'] = $campaign->dimensions->stat_time_day;
                        $i++;
                    }
                }
                CampaignData::upsert($campaignData, ['campaign_id'], ['campaign_id', 'cpm', 'ctr', 'cpc', 'reach', 'clicks', 'impressions', 'spend', 'stat_time_day']);
            } catch (Exception $e) {
                //Mail::to(env('MAIL_TO'))->send(new Mailer($e));
            }
        }
        return true;
    }

    //////////////////// End Campaign ////////////////////////

    //////////////////// Start Ad Group ////////////////////////

    /* This function is used to insert and update ad group data */
    public function updateAdGroupData()
    {

        // Get all campaigns list from database
        $campaigns = Campaign::select('campaign_id', 'advertiser_id')->get();

        foreach ($campaigns as $campaign) {
            $advertiserId = $campaign->advertiser_id;
            $campaignId = $campaign->campaign_id;

            $this->updateIntegratedAdGroupData($advertiserId, $campaignId);
        }
        return true;
    }

    public function updateIntegratedAdGroupData($advertiserId, $campaignId)
    {
        $method = "reports/integrated/get/";

        // AdGroup data
        $params = [
            'advertiser_id' => $advertiserId,
            'report_type' => 'BASIC',
            'dimensions' => json_encode(['adgroup_id']),
            'metrics' => json_encode(['campaign_name', 'campaign_id', 'adgroup_name', 'spend', 'impressions', 'reach', 'stat_cost', 'cpc', 'cpm', 'show_cnt', 'click_cnt', 'ctr', 'show_uv']),
            'filters' => [[json_encode(['filter_value' => [$campaignId], 'field_name' => 'campaign_ids', 'filter_type' => 'IN'])]],
            'data_level' => 'AUCTION_ADGROUP',
            'lifetime' => true
        ];


        $adGroups = $this->curlGetExecution($method, $params);

        if ($adGroups->message == "OK") {
            $adGroupsData = [];
            $i = 0;

            try {
                if (isset($adGroups->data) && isset($adGroups->data->list) && count($adGroups->data->list)) {
                    foreach ($adGroups->data->list as $adGroup) {
                        $adGroupsData[$i]['campaign_id'] = $adGroup->metrics->campaign_id;
                        $adGroupsData[$i]['adgroup_id'] = $adGroup->dimensions->adgroup_id;
                        $adGroupsData[$i]['adgroup_name'] = $adGroup->metrics->adgroup_name;
                        $adGroupsData[$i]['total_cpm'] = $adGroup->metrics->cpm;
                        $adGroupsData[$i]['total_ctr'] = $adGroup->metrics->ctr;
                        $adGroupsData[$i]['total_cpc'] = $adGroup->metrics->cpc;
                        $adGroupsData[$i]['total_reach'] = $adGroup->metrics->reach;
                        $adGroupsData[$i]['total_clicks'] = $adGroup->metrics->clicks;
                        $adGroupsData[$i]['total_impressions'] = $adGroup->metrics->impressions;
                        $adGroupsData[$i]['total_spend'] = $adGroup->metrics->spend;
                        $i++;
                    }
                }
                AdGroup::upsert($adGroupsData, ['campaign_id', 'adgroup_id'], ['campaign_id', 'adgroup_id', 'adgroup_name', 'total_cpm', 'total_ctr', 'total_cpc', 'total_reach', 'total_clicks', 'total_impressions', 'total_spend']);
            } catch (Exception $e) {
                // Mail::to(env('MAIL_TO'))->send(new Mailer($e));
            }
        }
    }

    //////////////////// End Ad Group ////////////////////////

    //////////////////// Start Ads ////////////////////////

    /* This function is used to insert and update ads data */
    public function updateAdsData()
    {

        // Get all campaigns list from database
        $campaigns = Campaign::select('campaign_id', 'advertiser_id')->get();

        foreach ($campaigns as $campaign) {
            $advertiserId = $campaign->advertiser_id;
            $campaignId = $campaign->campaign_id;

            // Update data from ad api
            $this->updateAd($advertiserId, $campaignId);

            // Update data for TikTok Ads



            // Update data from integrated campaign api
            $this->updateIntegratedAd($advertiserId, $campaignId);
        }
        return true;
    }

    /* This function gets data from  ad/get/ and create or update ads */
    public function updateAd($advertiserId, $campaignId)
    {

        $method = "ad/get/";
        $params = [
            'advertiser_id' => $advertiserId,
            'fields' => json_encode(
                [
                    'campaign_id',
                    'adgroup_id',
                    'ad_id',
                    'ad_name',
                    'image_mode',
                    'opt_status',
                    'ad_text',
                    'image_ids',
                    'playable_url',
                    'ad_texts',
                    'profile_image',
                    'landing_page_urls',
                    'call_to_action_id',
                    'landing_page_url',
                    'external_action',
                    'is_aco',
                    'is_creative_authorized',
                    'status',
                    'tiktok_item_id',
                    'call_to_action',
                    'page_id',
                    'video_id',
                    'display_name',
                    'open_url_type',
                    'open_url',
                    'impression_tracking_url',
                    'vast_moat',
                    'pixel_id',
                    'click_tracking_url',
                    'is_new_structure',
                    'app_name',
                    'create_time',
                    'modify_time'
                ]
            ),
            'filtering' => json_encode(['campaign_ids' => [$campaignId]])
        ];

        $adsData = [];

        $ads = $this->curlGetExecution($method, $params,30);


        $i = 0;

        if ($ads->message == "OK") {

            try {

                if (isset($ads->data) && isset($ads->data->list) && count($ads->data->list)) {
                    foreach ($ads->data->list as $ad) {

                        $adsData[$i]['campaign_id'] = $ad->campaign_id;
                        $adsData[$i]['adgroup_id'] = $ad->adgroup_id;
                        $adsData[$i]['ad_name'] = $ad->ad_name;
                        $adsData[$i]['ad_id'] = $ad->ad_id;
                        $adsData[$i]['image_mode'] = isset($ad->image_mode) ? $ad->image_mode : null;
                        $adsData[$i]['opt_status'] = isset($ad->opt_status) ? $ad->opt_status : null;
                        $adsData[$i]['ad_text'] = isset($ad->ad_text) ? $ad->ad_text : null;
                        $adsData[$i]['image_ids'] = serialize(isset($ad->image_ids) ? $ad->image_ids : []);
                        $adsData[$i]['playable_url'] = isset($ad->playable_url) ? $ad->playable_url : null;
                        $adsData[$i]['ad_texts'] = isset($ad->ad_texts) ? $ad->ad_texts : null;
                        $adsData[$i]['profile_image'] = isset($ad->profile_image) ? $ad->profile_image : null;
                        $adsData[$i]['landing_page_urls'] = isset($ad->landing_page_urls) ? $ad->landing_page_urls : null;
                        $adsData[$i]['call_to_action_id'] = isset($ad->call_to_action_id) ? $ad->call_to_action_id : null;
                        $adsData[$i]['landing_page_url'] = isset($ad->landing_page_url) ? $ad->landing_page_url : null;
                        $adsData[$i]['external_action'] = isset($ad->external_action) ? $ad->external_action : null;
                        $adsData[$i]['is_aco'] = isset($ad->is_aco) ? $ad->is_aco : null;
                        $adsData[$i]['is_creative_authorized'] = isset($ad->is_creative_authorized) ? $ad->is_creative_authorized : null;
                        $adsData[$i]['status'] = isset($ad->status) ? $ad->status : null;
                        $adsData[$i]['tiktok_item_id'] = isset($ad->tiktok_item_id) ? $ad->tiktok_item_id : null;
                        $adsData[$i]['call_to_action'] = isset($ad->call_to_action) ? $ad->call_to_action : null;
                        $adsData[$i]['page_id'] = isset($ad->page_id) ? $ad->page_id : null;
                        $adsData[$i]['video_id'] = isset($ad->video_id) ? $ad->video_id : null;
                        $adsData[$i]['display_name'] = isset($ad->display_name) ? $ad->display_name : null;
                        $adsData[$i]['open_url_type'] = isset($ad->open_url_type) ? $ad->open_url_type : null;
                        $adsData[$i]['open_url'] = isset($ad->open_url) ? $ad->open_url : null;
                        $adsData[$i]['impression_tracking_url'] = isset($ad->impression_tracking_url) ? $ad->impression_tracking_url : null;
                        $adsData[$i]['vast_moat'] = isset($ad->vast_moat) ? $ad->vast_moat : null;
                        $adsData[$i]['pixel_id'] = isset($ad->pixel_id) ? $ad->pixel_id : null;
                        $adsData[$i]['click_tracking_url'] = isset($ad->click_tracking_url) ? $ad->click_tracking_url : null;
                        $adsData[$i]['is_new_structure'] = isset($ad->is_new_structure) ? $ad->is_new_structure : null;
                        $adsData[$i]['app_name'] = isset($ad->app_name) ? $ad->app_name : null;
                        $adsData[$i]['create_time'] = $ad->create_time;
                        $adsData[$i]['modify_time'] = $ad->modify_time;
                        $i++;
                    }
                }
                Ad::upsert(
                    $adsData,
                    ['ad_id'],
                    [
                        'campaign_id',
                        'adgroup_id',
                        'ad_id',
                        'ad_name',
                        'image_mode',
                        'opt_status',
                        'ad_text',
                        'image_ids',
                        'playable_url',
                        'ad_texts',
                        'profile_image',
                        'landing_page_urls',
                        'call_to_action_id',
                        'landing_page_url',
                        'external_action',
                        'is_aco',
                        'is_creative_authorized',
                        'status',
                        'tiktok_item_id',
                        'call_to_action',
                        'page_id',
                        'video_id',
                        'display_name',
                        'open_url_type',
                        'open_url',
                        'impression_tracking_url',
                        'vast_moat',
                        'pixel_id',
                        'click_tracking_url',
                        'is_new_structure',
                        'app_name',
                        'create_time',
                        'modify_time'
                    ]
                );
            } catch (Exception $e) {
                //Mail::to(env('MAIL_TO'))->send(new Mailer($e));
            }
        }

        return true;
    }

    public function updateIntegratedAd($advertiserId, $campaignId)
    {
        $method = "reports/integrated/get/";

        // AdGroup data
        $params = [
            'advertiser_id' => $advertiserId,
            'report_type' => 'BASIC',
            'dimensions' => json_encode(['ad_id']),
            'metrics' => json_encode(['campaign_name', 'campaign_id', 'adgroup_id', 'ad_name', 'spend', 'impressions', 'reach', 'stat_cost', 'cpc', 'cpm', 'show_cnt', 'click_cnt', 'ctr', 'show_uv']),
            'filters' => [[json_encode(['filter_value' => [$campaignId], 'field_name' => 'campaign_ids', 'filter_type' => 'IN'])]],
            'data_level' => 'AUCTION_AD',
            'lifetime' => true
        ];


        $ads = $this->curlGetExecution($method, $params,30);

        if ($ads->message == "OK") {
            $adsData = [];
            $i = 0;

            try {

                if (isset($ads->data) && isset($ads->data->list) && count($ads->data->list)) {
                    foreach ($ads->data->list as $ads) {
                        $adsData[$i]['campaign_id'] = $ads->metrics->campaign_id;
                        $adsData[$i]['adgroup_id'] = $ads->metrics->adgroup_id;
                        $adsData[$i]['ad_id'] = $ads->dimensions->ad_id;
                        $adsData[$i]['ad_name'] = $ads->metrics->ad_name;
                        $adsData[$i]['total_cpm'] = $ads->metrics->cpm;
                        $adsData[$i]['total_ctr'] = $ads->metrics->ctr;
                        $adsData[$i]['total_cpc'] = $ads->metrics->cpc;
                        $adsData[$i]['total_reach'] = $ads->metrics->reach;
                        $adsData[$i]['total_clicks'] = $ads->metrics->clicks;
                        $adsData[$i]['total_impressions'] = $ads->metrics->impressions;
                        $adsData[$i]['total_spend'] = $ads->metrics->spend;
                        $i++;
                    }
                }
                Ad::upsert($adsData, ['ad_id'], ['campaign_id', 'adgroup_id', 'ad_id', 'ad_name', 'total_cpm', 'total_ctr', 'total_cpc', 'total_reach', 'total_clicks', 'total_impressions', 'total_spend']);
            } catch (Exception $e) {
                //Mail::to(env('MAIL_TO'))->send(new Mailer($e));
            }
        }
    }

    /* This function is used to save Auth code for Ads */
    public function updateTikTokAuthCode()
    {
        $method = "tt_video/list/";

        // Get all ads tiktok data
        $advertisers = Advertiser::all()->pluck('advertiser_id')->toArray();
        

        foreach ($advertisers as $advertiserId) {
            //$advertiserId = 6963691513947619330;
            $params = [
                'advertiser_id' => $advertiserId
            ];
            
            $items = $this->curlGetExecution($method, $params);


            try{

            if ($items->message == "OK") {
                
                if (isset($items->data) && isset($items->data->list) && count($items->data->list)) {
                    $itemsList = $items->data->list;

                    foreach($itemsList as $item)
                    {
                        $item_info = $item->item_info;
                        $auth_info = $item->auth_info;

                        if($auth_info->ad_auth_status && $auth_info->ad_auth_status == "AUTHORIZED")
                        {
                            $auth_start_time = $auth_info->auth_start_time;
                            $auth_end_time = $auth_info->auth_end_time;
                            $item_auth_code = $item_info->auth_code;
                            $item_id = $item_info->item_id;



                            // Update Ad Auth info
                            Ad::where('tiktok_item_id',$item_id)->update([
                                    'auth_code' => $item_auth_code,
                                    'auth_start_time' => $auth_start_time,
                                    'auth_end_time' => $auth_end_time
                            ]);
                        }
                    }
                }
            }
        }
        catch(Exception $e)
        {
           
        }
            
       }

    }

    /* This function is used to add videos for tiktok items */
    public function updateVideosForTiktokItems()
    {

        $method = 'tt_video/info/';

        $adsList = Ad::join('campaigns', 'campaigns.campaign_id', '=', 'ads.campaign_id')
                        ->whereNotNull('tiktok_item_id')
                        ->select('ads.*', 'campaigns.advertiser_id')
                        ->get()->all();

        //print($adsList[0]);
        $i=0;
        $videosData = [];

        foreach($adsList as $ad)
        {
            $advertiserId = $ad->advertiser_id;
            $authCode = $ad->auth_code;

            if(!is_null($authCode))
            {
                $params = [
                    'advertiser_id' => $advertiserId,
                    'auth_code' => $authCode
                ];
               
                $videoInfo = $this->curlGetExecution($method, $params);
               
                try {
                    if($videoInfo->code == 0 && $videoInfo->message == "OK")
                    {
                        if(isset($videoInfo->data))
                        {

                            $video = $videoInfo->data->video_info;
                            $videosData[$i]['ad_id'] = $ad->ad_id;
                            $videosData[$i]['url'] = isset($video->url) ? $video->url : null;
                            $videosData[$i]['duration'] = isset($video->duration) ? $video->duration : null;
                            $videosData[$i]['poster_url'] = isset($video->poster_url) ? $video->poster_url : null;
                            $videosData[$i]['signature'] = isset($video->signature) ? $video->signature : null;
                            $videosData[$i]['bit_rate'] =  isset($video->bit_rate) ? $video->bit_rate : null;
                            $videosData[$i]['size'] = isset($video->size) ? $video->size : null;
                            $videosData[$i]['height'] = isset($video->height) ? $video->height : null;
                            $videosData[$i]['width'] = isset($video->width) ? $video->width : null;
                            $videosData[$i]['file_name'] = isset($video->file_name) ? $video->file_name : null;
                            $i++;
                        }
                        
                    }
                }
                catch (Exception $e)
                {

                }
            }  
        }

       
        Video::upsert(
            $videosData,
            ['ad_id'],
            [
                'ad_id',
                'url',
                'duration',
                'poster_url',
                'signature',
                'bit_rate',
                'size',
                'height',
                'width',
                'file_name'
            ]
        );
        
    }

    /* This function is used to insert and update ads data for each day */
    public function updateAdsDataForEachDay()
    {

        // Get all ads list from database
        $ads = Ad::select('ad_id', 'campaigns.advertiser_id', 'ads.campaign_id', 'ads.create_time')
            ->join('campaigns', 'campaigns.campaign_id', '=', 'ads.campaign_id')
            ->whereNotNull('ads.create_time')
            ->get();

        foreach ($ads as $ad) {
            $advertiserId = $ad->advertiser_id;
            $adId = $ad->ad_id;

            $adCreateTime = $ad->create_time;
            $adStartDate = date('Y-m-d', strtotime($adCreateTime));


            /* Check Ads data */

            $adsData =  AdData::where('ad_id', $adId)->orderBy('stat_time_day', 'DESC');
            if ($adsData->count() > 0) {
                $adsData  = $adsData->latest()->first();
                $adStartDate = Carbon::parse($adsData->stat_time_day)->format('Y-m-d');
            }

            /* Check Ads data */

            // Should not greater than today
            $now = Carbon::now()->format('Y-m-d');

            while (strtotime($adStartDate) <= strtotime($now)) {
                $dateAfterAddingDays = Carbon::parse($adStartDate)->addDays(20);
                $dateAfterAddingDays = $dateAfterAddingDays->format('Y-m-d');

                if (strtotime($dateAfterAddingDays)  >= strtotime($now)) {
                    $dateAfterAddingDays = $now;
                    $this->updateIntegratedAdsDataForEachDay($advertiserId, $adId, $adStartDate, $dateAfterAddingDays);
                    break;
                }
                $this->updateIntegratedAdsDataForEachDay($advertiserId, $adId, $adStartDate, $dateAfterAddingDays);

                $adStartDate = $dateAfterAddingDays;
            }
        }
    }

    /* This function is used to insert ads data */
    public function updateIntegratedAdsDataForEachDay($advertiserId, $adId, $startDate, $endDate)
    {
        $method = "reports/integrated/get/";

        // Campaign data
        $params = [
            'advertiser_id' => $advertiserId,
            'report_type' => 'BASIC',
            'dimensions' => json_encode(['ad_id', 'stat_time_day']),
            'metrics' => json_encode(['spend', 'impressions', 'reach', 'stat_cost', 'cpc', 'cpm', 'show_cnt', 'click_cnt', 'ctr', 'show_uv']),
            'filters' => json_encode([['filter_value' => json_encode([$adId]), 'field_name' => 'ad_ids', 'filter_type' => 'IN']]),
            'data_level' => 'AUCTION_AD',
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        $ads = $this->curlGetExecution($method, $params, 20);

        if ($ads->message == "OK") {
            $adsData = [];
            $i = 0;

            try {
                if (isset($ads->data) && isset($ads->data->list) && count($ads->data->list)) {
                    foreach ($ads->data->list as $ad) {
                        $adsData[$i]['ad_id'] = $ad->dimensions->ad_id;
                        $adsData[$i]['cpm'] = $ad->metrics->cpm;
                        $adsData[$i]['ctr'] = $ad->metrics->ctr;
                        $adsData[$i]['cpc'] = $ad->metrics->cpc;
                        $adsData[$i]['reach'] = $ad->metrics->reach;
                        $adsData[$i]['clicks'] = $ad->metrics->clicks;
                        $adsData[$i]['impressions'] = $ad->metrics->impressions;
                        $adsData[$i]['spend'] = $ad->metrics->spend;
                        $adsData[$i]['stat_time_day'] = $ad->dimensions->stat_time_day;
                        $i++;
                    }
                }
                AdData::upsert($adsData, ['ad_id'], ['ad_id', 'cpm', 'ctr', 'cpc', 'reach', 'clicks', 'impressions', 'spend', 'stat_time_day']);
            } catch (Exception $e) {
                //Mail::to(env('MAIL_TO'))->send(new Mailer($e));
            }
        }
        return true;
    }

    //////////////////// End Ad Group ////////////////////////


    //////////////////// Start Videos ////////////////////////

    /* This function is used to insert and update videos data */
    public function updateVideosData()
    {

        // Get all ads list from database
        $ads = Ad::select('ad_id', 'video_id', 'campaigns.advertiser_id', 'ads.create_time')
            ->join('campaigns', 'campaigns.campaign_id', '=', 'ads.campaign_id')
            ->whereNotNull('ads.video_id')
            ->get();

        foreach ($ads as $ad) {
            $advertiserId = $ad->advertiser_id;
            $adId = $ad->ad_id;
            $videoId = $ad->video_id;

            // Update data from ad api
            $this->updateVideos($advertiserId, $adId, $videoId);
        }
        return true;
    }

    /* This function gets data from  ad/get/ and create or update ads */
    public function updateVideos($advertiserId, $adId, $videoId)
    {

        $method = "file/video/ad/info/";
        $params = [
            'advertiser_id' => $advertiserId,
            'video_ids' => json_encode([$videoId])
        ];

        $videosData = [];

        $videos = $this->curlGetExecution($method, $params);

        $i = 0;

        if ($videos->message == "OK") {

            try {
                if (isset($videos->data) && isset($videos->data->list) && count($videos->data->list)) {
                    foreach ($videos->data->list as $video) {

                        $videosData[$i]['ad_id'] = $adId;
                        $videosData[$i]['material_id'] = $video->material_id;
                        $videosData[$i]['url'] = isset($video->url) ? $video->url : null;
                        $videosData[$i]['displayable'] = isset($video->displayable) ? $video->displayable : null;
                        $videosData[$i]['duration'] = isset($video->duration) ? $video->duration : null;
                        $videosData[$i]['allowed_placements'] = isset($video->allowed_placements) ? serialize($video->allowed_placements) : null;
                        $videosData[$i]['poster_url'] = isset($video->poster_url) ? $video->poster_url : null;
                        $videosData[$i]['signature'] = isset($video->signature) ? $video->signature : null;
                        $videosData[$i]['allow_download'] = isset($video->allow_download) ? $video->allow_download : null;
                        $videosData[$i]['bit_rate'] =  isset($video->bit_rate) ? $video->bit_rate : null;
                        $videosData[$i]['size'] = isset($video->size) ? $video->size : null;
                        $videosData[$i]['format'] = isset($video->format) ? $video->format : null;
                        $videosData[$i]['height'] = isset($video->height) ? $video->height : null;
                        $videosData[$i]['width'] = isset($video->width) ? $video->width : null;
                        $videosData[$i]['file_name'] = isset($video->file_name) ? $video->file_name : null;
                        $videosData[$i]['create_time'] = date('Y-m-d H:i:s', strtotime($video->create_time));
                        $videosData[$i]['modify_time'] = date('Y-m-d H:i:s', strtotime($video->modify_time));
                        $i++;
                    }
                }

                Video::upsert(
                    $videosData,
                    ['ad_id'],
                    [
                        'ad_id',
                        'material_id',
                        'url',
                        'displayable',
                        'duration',
                        'allowed_placements',
                        'poster_url',
                        'signature',
                        'allow_download',
                        'bit_rate',
                        'size',
                        'format',
                        'height',
                        'width',
                        'file_name',
                        'create_time',
                        'modify_time'
                    ]
                );
            } catch (Exception $e) {
                //Mail::to(env('MAIL_TO'))->send(new Mailer($e));
            }
        }

        return true;
    }

    //////////////////// End Videos ////////////////////////

}
