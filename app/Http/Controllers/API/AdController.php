<?php

namespace App\Http\Controllers\API;

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
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

use Jose\Factory\JWKFactory;
use Jose\Loader;

class AdController extends Controller
{

    public $userInfo;
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        //$this->middleware('auth');
        $accessToken = $request->header('accessToken');

        if (isset($accessToken)) {
            $userInfo = $this->decryptData($accessToken);
            $this->userInfo = $userInfo;
            
            if ($userInfo['isTokenExpired'] == true) {                
                $result = ['status' => false, 'statusCode' => 401, 'message' => 'Token is expired!', 'data' => []];
                echo json_encode($result);
                exit;
            }
        }        
    }

    /**
     * Show the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */


    public function index()
    {
        return view('index');
    }

    public function login()
    {
        return view('login');
    }

    public function decryptData($accessToken)
    {

        $result = [];

        $jku = env('AWS_JKU');

        $jwk_set = JWKFactory::createFromJKU($jku);

        // We create our loader.
        $loader = new Loader();

        // This is the token we want to load and verify.
        $token = $accessToken;

        // The signature is verified using our key set.
        if ($token) {
            try {
                $jws = $loader->loadAndVerifySignatureUsingKeySet(
                    $token,
                    $jwk_set,
                    ['RS256'],
                    $signature_index
                );

                $payLoadInfo = $jws->getPayload(); // contains the username, sub, expiry and other details for use in your application                 
                $isTokenExpired = ($payLoadInfo['exp'] - time()) < 0;

                $userType = $payLoadInfo["cognito:groups"][0];
                $username = $payLoadInfo["cognito:username"];


                $advertisersList = [];
                if (isset($payLoadInfo["custom:clients"]) && !empty($payLoadInfo["custom:clients"])) {
                    $advertisersList = explode('|', $payLoadInfo["custom:clients"]);
                }

                $result = ['isTokenExpired' => $isTokenExpired, 'username' => $username, 'type' => $userType, 'advertisers' => $advertisersList];
            } catch (Exception $e) {
                $result = ['isTokenExpired' => false, 'error' => $e->getMessage()];
            }
        }

        // if (isset($accessToken)) {
        //     $tokenParts = explode(".", $accessToken);
        //     $tokenHeader = base64_decode($tokenParts[0]);
        //     $tokenPayload = base64_decode($tokenParts[1]);
        //     $jwtHeader = json_decode($tokenHeader);
        //     $jwtPayload = json_decode($tokenPayload);
        //     $payLoadInfo = (array)$jwtPayload;

        //     $userType = $payLoadInfo["cognito:groups"][0];
        //     $username = $payLoadInfo["cognito:username"];

        //     $advertisersList = [];
        //     if (isset($payLoadInfo["custom:clients"]) && !empty($payLoadInfo["custom:clients"])) {
        //         $advertisersList = explode('|', $payLoadInfo["custom:clients"]);
        //     }

        //     $result = ['username' => $username, 'type' => $userType, 'advertisers' => $advertisersList];
        // }
        return $result;
    }

    public function authentication(Request $request)
    {
        $accessToken  = $request->header('accessToken');
        $result = ['status' => false, 'statusCode' => 401, 'message' => 'Invalid Username or Password.', 'data' => []];
        if (isset($accessToken)) {
            $userInfo = $this->userInfo;
            
            if (count($userInfo) > 0) {
                $result = ['status' => true, 'statusCode' => 200, 'message' => 'User has been logged in successfully!', 'data' => []];
            }
        }
        return response()->json($result);
    }

    public function clients(Request $request)
    {

        $result = ['status' => false, 'statusCode' => 401, 'message' => 'Server error!', 'data' => []];
        $isAdmin = false;

        $accessToken = $request->header('accessToken');

        if (isset($accessToken)) {
            $userInfo = $this->userInfo;

            

            $username = $userInfo['username'];
            $type = $userInfo['type'];
            $advertiserArray = $userInfo['advertisers'];

            if ($type == 'Admins') {
                $isAdmin = true;
            }


            if ($type == "Admins") {
                $advertisers = Advertiser::select('advertiser_id', 'advertiser_name', 'image')->get();
            } else {
                $advertisers = Advertiser::whereIn('advertiser_id', $advertiserArray)->select('advertiser_id', 'advertiser_name', 'image')->get();
            }

            $result = ['status' => true, 'statusCode' => 200, 'message' => '', 'data' => ['advertisers' => $advertisers, 'isAdmin' => $isAdmin]];
        }
        return $result;
    }



    /* Save and Update advertisers */
    public function saveAdvertisers($advertisers, $access_token)
    {
        if ($advertisers->message = "OK") {

            $advertiserData = [];

            foreach ($advertisers->data->list as $advertiser) {
                $advertiser = (array)$advertiser;
                $advertiser['access_token'] = $access_token;
                $advertiserData[] = (array)$advertiser;
            }

            Advertiser::upsert($advertiserData, ['advertiser_id']);
        }
    }

    public function getCampaigns(Request $request)
    {


        $result = ['status' => false, 'statusCode' => 401, 'message' => 'Server error!', 'data' => ['advertisers' => [], 'allCampaigns' => []]];
        $accessToken  = $request->header('accessToken');
        $advertiserId = $request->advertiserId;
        $campaignId = $request->campaignId;
        $isAdmin = false;
        if (isset($accessToken)) {

            $userInfo = $this->userInfo;

            

            $username = $userInfo['username'];
            $type = $userInfo['type'];
            $advertiserArray = $userInfo['advertisers'];

            $advertisers = Advertiser::select('advertiser_id', 'advertiser_name', 'image')->where('advertiser_id', $advertiserId)->get();

            $campaignReach = 0;
            $campaignClicks = 0;
            $campaignSpend = 0;
            $campaignCpm = 0;
            $campaignCtr = 0;
            $campaignImpressions = 0;

            if ($type == 'Admins') {
                $isAdmin = true;
            }


            ///////////////// Campaign Data //////////////////////

            $allCampaigns = Campaign::where('campaigns.advertiser_id', $advertiserId);

            if (!$isAdmin) {
                $allCampaigns->whereIn('campaigns.advertiser_id', $advertiserArray);
            }

            $allCampaigns->join('advertisers', 'advertisers.advertiser_id', '=', 'campaigns.advertiser_id');
            $allCampaigns->select('campaigns.id', 'campaigns.campaign_id', 'campaigns.campaign_name', 'advertisers.advertiser_name');

            $allCampaigns = $allCampaigns->get();


            $campaigns = Campaign::where('campaigns.advertiser_id', $advertiserId);

            $campaigns->join('advertisers', 'advertisers.advertiser_id', '=', 'campaigns.advertiser_id');

            if (!$isAdmin) {
                $campaigns->whereIn('advertisers.advertiser_id', $advertiserArray);
            }

            if (!empty($campaignId)) {
                $campaigns->where('campaigns.campaign_id', $campaignId);
            }
            $campaigns->select('campaigns.*', 'advertisers.advertiser_name', 'advertisers.image');

            $campaigns = $campaigns->get();
            $i = 0;
            $campaign_updated_time = "N/A";
            if ($campaigns->count() > 0) {
                foreach ($campaigns as $campaign) {

                    if ($i == 0) {

                        $campaign_updated_time = (isset($campaign->updated_at) && $campaign->updated_at != null) ? date('h.ia T, m.d.y', strtotime($campaign->updated_at)) : "N/A";
                    }

                    if ($campaignId == $campaign->campaign_id) {

                        $campaignImpressions = $campaign->total_impressions;
                        $campaignReach = $campaign->total_reach;
                        $campaignClicks = $campaign->total_clicks;
                        $campaignSpend = (!empty($campaign->custom_total_spend) && !is_null($campaign->custom_total_spend)) ? $campaign->custom_total_spend : 0;
                        $campaignCtr = $campaign->total_ctr;
                    }

                    if ($campaignId == "") {
                        $campaignImpressions += $campaign->total_impressions;
                        $campaignReach += $campaign->total_reach;
                        $campaignClicks += $campaign->total_clicks;
                        $campaignSpend += (!empty($campaign->custom_total_spend) && !is_null($campaign->custom_total_spend)) ? $campaign->custom_total_spend : 0;
                        $campaignCtr += $campaign->total_ctr;
                    }

                    $i++;
                }
            }


            if ($campaignImpressions != 0) {
                $campaignCtr = ($campaignClicks / $campaignImpressions) * 100;
                $campaignCpm = ($campaignSpend <= 0) ? 0 : ($campaignSpend / $campaignImpressions) * 1000;
            }

            $campaignImpressions = number_format($campaignImpressions);
            $campaignReach = number_format($campaignReach);
            $campaignClicks = number_format($campaignClicks);
            $campaignCtr = number_format($campaignCtr, 2) . "%";

            $campaignSpendTextField = ($campaignSpend <= 0) ? "" : number_format($campaignSpend, 2);
            $campaignCpmTextField = ($campaignCpm <= 0) ? "" : number_format($campaignCpm, 2);
            $campaignSpend = ($campaignSpend == 0) ? "—" : number_format($campaignSpend, 2);
            $campaignCpm = ($campaignCpm == 0) ? "—" : number_format($campaignCpm, 2);


            ///////////////// Campaign Data //////////////////////


            ///////////////// Ads Data //////////////////////

            $ads = Ad::join('campaigns', 'campaigns.campaign_id', '=', 'ads.campaign_id')
                ->join('advertisers', 'advertisers.advertiser_id', '=', 'campaigns.advertiser_id')
                ->join('adgroups', 'adgroups.adgroup_id', '=', 'ads.adgroup_id')
                ->leftJoin('videos', 'videos.ad_id', '=', 'ads.ad_id');
            if (!empty($campaignId)) {
                $ads->where('campaigns.campaign_id', $campaignId);
            }
            if (!empty($advertiserId)) {
                $ads->where('advertisers.advertiser_id', $advertiserId);
            }

            if (!$isAdmin) {
                $ads->whereIn('advertisers.advertiser_id', $advertiserArray);
            }

            $ads->select('ads.*', 'videos.url', 'videos.poster_url', 'adgroups.adgroup_name', 'campaigns.campaign_name');
            $ads->orderBy('ads.campaign_id');
            $ads->orderBy('ads.adgroup_id');
            $ads =  $ads->get();


            $campaignColSpan = [];
            $adGroupColspan = [];

            $adsData = [];
            $i = 0;
            if ($ads->count() > 0) {

                foreach ($ads as $ad) {

                    $adsData[$i]['ad_id'] = $ad->ad_id;
                    $adsData[$i]['ad_name'] = $ad->ad_name;

                    $adsData[$i]['ad_url'] = 'javascript:;';
                    if ($ad->url != null) {
                        $adsData[$i]['ad_url'] = $ad->url;
                    }
                    $adsData[$i]['ad_text'] = $ad->ad_text;
                    $adsData[$i]['poster_url'] = null;
                    if ($this->img_exist($ad->poster_url)) {
                        $adsData[$i]['poster_url'] = $ad->poster_url;
                    }

                    $adsData[$i]['total_impressions'] = $ad->total_impressions;
                    $adsData[$i]['total_reach'] = $ad->total_reach;
                    $adsData[$i]['total_clicks'] = $ad->total_clicks;
                    $adsData[$i]['total_ctr'] = $ad->total_ctr;


                    // Colspan Count

                    if (!array_key_exists($ad->campaign_id, $campaignColSpan)) {
                        $campaignColSpan[$ad->campaign_id] = 0;
                    }

                    if (!array_key_exists($ad->adgroup_id, $adGroupColspan)) {
                        $adGroupColspan[$ad->adgroup_id] = 0;
                    }

                    $campaignColSpan[$ad->campaign_id]++;
                    $adGroupColspan[$ad->adgroup_id]++;


                    $i++;
                }
            }


            ///////////////// Ads Data //////////////////////


            //////////////// Review Data /////////////////////

            $reviews = Review::where('advertiser_id', $advertiserId);

            if (!$isAdmin) {
                $reviews->whereIn('advertiser_id', $advertiserArray);
            }

            if (!empty($campaignId)) {
                $reviews->where('campaign_id', $campaignId);
            }
            $reviews->orderBy('created_at', 'DESC');

            $reviews = $reviews->get();
            $reviewFilterOptions = [];

            foreach ($reviews as $review) {
                if (!in_array($review->created_at, $reviewFilterOptions)) {
                    array_push($reviewFilterOptions, $review->created_at);
                }
            }

            //////////////// Review Data /////////////////////


            $cron =  Cron::where('id', 1)->get();



            if ($cron->count()) {

                $campaign_updated_time = $cron[0]->updated_at;
            }


            $campaignStartDate = Carbon::now()->format('Y-m-d');
            $campaignReachData = $this->getCamapignDataForDays($advertiserId, $campaignId);
            if ($campaignReachData->count() > 0) {
                foreach ($campaignReachData as $reachData) {
                    $campaignStartDate = date('Y-m-d', strtotime($reachData['stat_time_day']));
                    break;
                }
            }

            $result = ['status' => true, 'statusCode' => 200, 'message' => '', 'data' => [
                'isAdmin' => $isAdmin, 'campaign_updated_time' => $campaign_updated_time,
                'advertisers' => $advertisers, 'campaignStartDate' => $campaignStartDate, 'allCampaigns' => $allCampaigns, 'campaignImpressions' => $campaignImpressions,
                'campaignReach' => $campaignReach, 'campaignClicks' => $campaignClicks, 'campaignSpend' => $campaignSpend, 'campaignCpm' => $campaignCpm, 'campaignCtr' => $campaignCtr,
                'adsData' => $adsData, 'ads' => $ads,  'campaignColSpan' => $campaignColSpan, 'adGroupColspan' => $adGroupColspan, 'reviews' => $reviews, 'reviewFilterOptions' => $reviewFilterOptions
            ]];
        }
        return $result;


        $userLogin = $request->session()->get('userLogin');
        $advertisersList = [];
        if (!$userLogin) {
            return redirect('/');
        }

        $is_admin = $request->session()->get('is_admin');

        if (!$is_admin) {
            $advertisersList = $request->session()->get('advertisers');
            if ($advertiserId && !in_array($advertiserId, $advertisersList)) {
                return redirect('select-client');
            }
        }


        $darkMode = (isset($_COOKIE['darkMode']) && ($_COOKIE['darkMode'])) ? $_COOKIE['darkMode'] : 0;
        $campaign_id = $request->query('campaign_id');

        if (!isset($campaign_id)) {
            $campaign_id = "";
        }

        ///////////////// Campaign Data //////////////////////

        $allCampaigns = Campaign::where('campaigns.advertiser_id', $advertiserId);
        $allCampaigns->join('advertisers', 'advertisers.advertiser_id', '=', 'campaigns.advertiser_id');
        $allCampaigns->select('campaigns.*', 'advertisers.advertiser_name');

        $allCampaigns = $allCampaigns->get();

        $campaigns = Campaign::where('campaigns.advertiser_id', $advertiserId);
        $campaigns->join('advertisers', 'advertisers.advertiser_id', '=', 'campaigns.advertiser_id');

        if (!empty($campaign_id)) {
            $campaigns->where('campaigns.campaign_id', $campaign_id);
        }
        $campaigns->select('campaigns.*', 'advertisers.advertiser_name', 'advertisers.image');

        $campaigns = $campaigns->get();

        ///////////////// Campaign Data //////////////////////

        //////////////// Review Data /////////////////////

        $reviews = Review::where('advertiser_id', $advertiserId);

        if (!empty($campaign_id)) {
            $reviews->where('campaign_id', $campaign_id);
        }
        $reviews->orderBy('created_at', 'DESC');

        $reviews = $reviews->get();

        //////////////// Review Data /////////////////////

        ///////////////// Ads Data //////////////////////

        $ads = Ad::join('campaigns', 'campaigns.campaign_id', '=', 'ads.campaign_id')
            ->join('advertisers', 'advertisers.advertiser_id', '=', 'campaigns.advertiser_id')
            ->join('adgroups', 'adgroups.adgroup_id', '=', 'ads.adgroup_id')
            ->leftJoin('videos', 'videos.ad_id', '=', 'ads.ad_id');
        if (!empty($campaign_id)) {
            $ads->where('campaigns.campaign_id', $campaign_id);
        }
        if (!empty($advertiserId)) {
            $ads->where('advertisers.advertiser_id', $advertiserId);
        }
        $ads->select('ads.*', 'videos.url', 'videos.poster_url', 'adgroups.adgroup_name', 'campaigns.campaign_name');
        $ads->orderBy('ads.campaign_id');
        $ads->orderBy('ads.adgroup_id');
        $ads =  $ads->get();

        $campaignStartDate = Carbon::now()->format('Y-m-d');
        $campaignReachData = $this->getCamapignDataForDays($advertiserId, $campaign_id);
        if ($campaignReachData->count() > 0) {
            foreach ($campaignReachData as $reachData) {
                $campaignStartDate = date('Y-m-d', strtotime($reachData['stat_time_day']));
                break;
            }
        }

        ///////////////// Ads Data //////////////////////

        //dd(['categories' => $categories, 'campaignGraphReach' => $campaignGraphReach]);
        return view('dashboard', [
            'campaigns' => $campaigns,
            'allCampaigns' => $allCampaigns,
            'ads' => $ads,
            'advertiserId' => $advertiserId,
            'darkMode' => $darkMode,
            'is_admin' => $is_admin,
            'reviews' => $reviews,
            'campaignStartDate' => $campaignStartDate,
            'advertisersList' => $advertisersList
        ]);
    }

    /* This function is used to cehck image URL */
    public function img_exist($url = NULL)
    {
        if (!$url) return false;

        $noimage = false;

        $headers = get_headers($url);
        return stripos($headers[0], "200 OK") ? $url : $noimage;
    }


    /* This function is used to get campaign reach data for graph */
    public function campaignReachData(Request $request)
    {
        $result = ['status' => false, 'statusCode' => 401, 'message' => 'Server error!', 'data' => ['advertisers' => [], 'allCampaigns' => []]];

        $accessToken  = $request->header('accessToken');

        $advertiserId = $request->advertiserId;
        $campaignId = $request->campaignId;
        $startDate = $request->startDate;
        $endDate = $request->endDate;

        if (isset($accessToken)) {

            $userInfo = $this->userInfo;

            

            $username = $userInfo['username'];
            $type = $userInfo['type'];
            $advertiserArray = $userInfo['advertisers'];
        }


        $campaignReachData = $this->getCamapignDataForDays($advertiserId, $campaignId, $startDate, $endDate);

        // Data for chart
        $reachDataForMap = [];


        foreach ($campaignReachData as $reachData) {

            if (!array_key_exists(date('m-d-Y', strtotime($reachData['stat_time_day'])), $reachDataForMap)) {
                $reachDataForMap[date('m-d-Y', strtotime($reachData['stat_time_day']))]['dateAndMonth'] = date('d M', strtotime($reachData['stat_time_day']));
                $reachDataForMap[date('m-d-Y', strtotime($reachData['stat_time_day']))]['campaignReach'] = 0;
            }
            $reachDataForMap[date('m-d-Y', strtotime($reachData['stat_time_day']))]['campaignReach'] += $reachData['reach'];
        }

        $result =  ['status' => true,  'statusCode' => 200, 'message' => '', 'data' => ['reachDataForMap' => $reachDataForMap]];

        return $result;
    }

    /* This function is used to get campaign data for days */
    public function getCamapignDataForDays($advertiserId, $campaignId, $startDate = "", $endDate = "")
    {
        $startDate = empty($startDate) ? "1970-01-01" : $startDate;
        $endDate = empty($endDate) ? date("Y-m-d") : $endDate;

        $campaignReach = CampaignData::join('campaigns', 'campaigns.campaign_id', '=', 'campaign_data.campaign_id');
        $campaignReach->join('advertisers', 'advertisers.advertiser_id', '=', 'campaigns.advertiser_id');
        $campaignReach->where('advertisers.advertiser_id', $advertiserId);
        if (!empty($campaignId)) {
            $campaignReach->where('campaigns.campaign_id', $campaignId);
        }
        $campaignReach->whereDate('campaign_data.stat_time_day', '>=', $startDate);
        $campaignReach->whereDate('campaign_data.stat_time_day', '<=', $endDate);

        $campaignReach->select('campaign_data.*', 'campaigns.campaign_name');
        $campaignReach->orderBy('campaign_data.stat_time_day', 'ASC');
        $campaignReach = $campaignReach->get();

        return $campaignReach;
    }


    public function setAccessToken(Request $request, $auth_code)
    {
        $postRequest = array(
            "secret" => env('TIKTOK_SECRET_KEY'),
            "app_id" => env('TIKTOK_API_ID'),
            "auth_code" => $auth_code
        );

        $cURLConnection = curl_init(env('TIKTOK_END_POINT') . 'oauth2/access_token_v2/');
        curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $postRequest);
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

        $apiResponse = curl_exec($cURLConnection);
        curl_close($cURLConnection);

        // $apiResponse - available data from the API request
        $jsonArrayResponse = json_decode($apiResponse);

        if ($jsonArrayResponse->message != "OK") {
            return redirect('/');
        }

        $access_token = $jsonArrayResponse->data->access_token;



        $request->session()->put('access_token', $access_token);
        $request->session()->put('advertiser_ids', $jsonArrayResponse->data->advertiser_ids);


        /* Set User Role */
        $method = "v1.1/bc/get/";
        $params = ['access_token' => $access_token];
        $userRolesData = $this->curlGetExecution($request, $method, $params);

        if ($userRolesData->message == "OK") {
            if (count($userRolesData->data->list) > 0) {
                $userRole = $userRolesData->data->list[0]->user_role;
                if ($userRole == "ADMIN") {
                    $request->session()->put('is_admin', true);
                } else {
                    $request->session()->put('is_admin', false);
                }
            }
        }

        /* Set User Role */
    }

    public function curlGetExecution(Request $request, $method, $params)
    {

        $access_token = $request->session()->get('access_token');
        //$access_token = '71049e128ae99c6046ac2bcd1f725be176fedd74';
        $queryString = http_build_query($params);

        $url = env('TIKTOK_END_POINT') . $method . "/?" . $queryString;

        $authorization = "Access-Token: " . $access_token;

        // $url = $tiktokEndPoint . 'v1.1/campaign/get/?advertiser_id=' . $advertiser_id
        $authorization = "Access-Token: " . $access_token;
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
        return $jsonArrayResponse;
    }


    public function getCsv(Request $request)
    {

        $accessToken  = $request->header('accessToken');

        $advertiserId = $request->advertiserId;
        $param_campaign_id = $request->campaignId; 

        $result = ['status' => false, 'statusCode' => 401, 'message' => 'Invalid Username or Password.', 'data' => []];
        if (isset($accessToken)) {
            $userInfo = $this->userInfo;
            

            if (!isset($param_campaign_id)) {
                $param_campaign_id = "";
            }

            $ads = Ad::join('campaigns', 'campaigns.campaign_id', '=', 'ads.campaign_id')
                ->join('advertisers', 'advertisers.advertiser_id', '=', 'campaigns.advertiser_id')
                ->join('adgroups', 'adgroups.adgroup_id', '=', 'ads.adgroup_id')
                ->join('ad_data', 'ad_data.ad_id', '=', 'ads.ad_id')
                ->leftJoin('videos', 'videos.ad_id', '=', 'ads.ad_id');
            if (!empty($param_campaign_id)) {
                $ads->where('campaigns.campaign_id', $param_campaign_id);
            }
            if (!empty($advertiserId)) {
                $ads->where('advertisers.advertiser_id', $advertiserId);
            }
            $ads->select(
                'ads.*',
                'videos.url',
                'videos.poster_url',
                'adgroups.adgroup_name',
                'campaigns.campaign_name',
                'ad_data.cpc',
                'ad_data.ctr',
                'ad_data.spend',
                'ad_data.cpm',
                'ad_data.impressions',
                'ad_data.clicks',
                'ad_data.reach',
                'ad_data.stat_time_day',

            );
            $ads->orderBy('ads.campaign_id');
            $ads =  $ads->get();

            $filename = date('Y-m-d_h.i.s_A') . ".csv";
            // Create an array of elements 
            $list = array(
                ['Campaign_Id', ' Campaign Name', 'Objective', 'AdGroup_Id', 'Ad Group Name', 'Ad_Id', 'Video_Id', 'Video_Url', 'Ad Name', 'Date', 'Impression', 'Click', 'CTR', 'Reach']
            );

            // Arrange Data
            foreach ($ads as $ad) {

                $campaign_id = $ad->campaign_id;
                $campaign_name = $ad->campaign_name;
                $adgroup_id = $ad->adgroup_id;
                $adgroup_name = $ad->adgroup_name;
                $ad_id = $ad->ad_id;
                $ad_name = $ad->ad_name;
                $impressions = $ad->impressions;
                $reach = $ad->reach;
                $clicks = $ad->clicks;
                $ctr = $ad->ctr;

                $video_id = (isset($ad->video_id) && $ad->video_id != null) ? $ad->video_id : "N/A";
                $video_url = (isset($ad->url) && $ad->url != null) ? $ad->url : "N/A";
                $created_date = $ad->stat_time_day;

                $itemData = [$campaign_id, $campaign_name, 'Reach',  $adgroup_id, $adgroup_name, $ad_id, $video_id, $video_url, $ad_name, $created_date, $impressions, $clicks, $ctr, $reach];
                array_push($list, $itemData);
            }

            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$filename",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );
            $callback = function() use($list){

            // Open a file in write mode ('w') 
            $f = fopen('php://output', 'w');

            // Loop through file pointer and a line 
            foreach ($list as $fields) {
                fputcsv($f, $fields);
            }
            // // reset the file pointer to the start of the file
            // fseek($f, 0);
            // // tell the browser it's going to be a csv file
            // header('Content-Type: application/csv');
            // // tell the browser we want to save it instead of displaying it
            // header('Content-Disposition: attachment; filename="' . $filename . '";');
            // // make php send the generated csv lines to the browser
            fclose($f);
            };
            return response()->stream($callback, 200, $headers);

        }

        
    }

    // Download PDF
    public function getPdf($advertiserId, Request $request)
    {

        $param_campaign_id = $request->query('campaign_id');

        if (!isset($param_campaign_id)) {
            $param_campaign_id = "";
        }

        $ads = Ad::join('campaigns', 'campaigns.campaign_id', '=', 'ads.campaign_id')
            ->join('advertisers', 'advertisers.advertiser_id', '=', 'campaigns.advertiser_id')
            ->join('adgroups', 'adgroups.adgroup_id', '=', 'ads.adgroup_id')
            ->join('ad_data', 'ad_data.ad_id', '=', 'ads.ad_id')
            ->leftJoin('videos', 'videos.ad_id', '=', 'ads.ad_id');
        if (!empty($param_campaign_id)) {
            $ads->where('campaigns.campaign_id', $param_campaign_id);
        }
        if (!empty($advertiserId)) {
            $ads->where('advertisers.advertiser_id', $advertiserId);
        }
        $ads->select(
            'ads.*',
            'videos.url',
            'videos.poster_url',
            'adgroups.adgroup_name',
            'campaigns.campaign_name',
            'ad_data.cpc',
            'ad_data.ctr',
            'ad_data.spend',
            'ad_data.cpm',
            'ad_data.impressions',
            'ad_data.clicks',
            'ad_data.reach',
            'ad_data.stat_time_day',

        );
        $ads->orderBy('ads.campaign_id');
        $ads =  $ads->get();

        $filename = date('Y-m-d_h.i.s_A') . ".pdf";
        $data = [];
        $pdf = PDF::loadView('get-pdf', ['ads' => $ads]);
        return $pdf->download($filename);
    }

    // Download Doc
    public function getDoc($advertiserId, Request $request)
    {

        $param_campaign_id = $request->query('campaign_id');

        if (!isset($param_campaign_id)) {
            $param_campaign_id = "";
        }

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();

        $ads = Ad::join('campaigns', 'campaigns.campaign_id', '=', 'ads.campaign_id')
            ->join('advertisers', 'advertisers.advertiser_id', '=', 'campaigns.advertiser_id')
            ->join('adgroups', 'adgroups.adgroup_id', '=', 'ads.adgroup_id')
            ->join('ad_data', 'ad_data.ad_id', '=', 'ads.ad_id')
            ->leftJoin('videos', 'videos.ad_id', '=', 'ads.ad_id');
        if (!empty($param_campaign_id)) {
            $ads->where('campaigns.campaign_id', $param_campaign_id);
        }
        if (!empty($advertiserId)) {
            $ads->where('advertisers.advertiser_id', $advertiserId);
        }
        $ads->select(
            'ads.*',
            'videos.url',
            'videos.poster_url',
            'adgroups.adgroup_name',
            'campaigns.campaign_name',
            'ad_data.cpc',
            'ad_data.ctr',
            'ad_data.spend',
            'ad_data.cpm',
            'ad_data.impressions',
            'ad_data.clicks',
            'ad_data.reach',
            'ad_data.stat_time_day',

        );
        $ads->orderBy('ads.campaign_id');
        $ads =  $ads->get();



        /////////////////

        $content = "";
        $campaignId = 0;
        $prevCampaignId = 0;
        $prevAdgroupId = 0;
        $campaignColSpan = [];
        $adGroupColspan = [];

        $adIds = [];

        foreach ($ads as $ad) {
            if (!array_key_exists($ad->campaign_id, $campaignColSpan)) {
                $campaignColSpan[$ad->campaign_id] = 0;
            }

            if (!array_key_exists($ad->adgroup_id, $adGroupColspan)) {
                $adGroupColspan[$ad->adgroup_id] = 0;
            }

            array_push($adIds, $ad->ad_id);
            $campaignColSpan[$ad->campaign_id]++;
            $adGroupColspan[$ad->adgroup_id]++;
        }


        $content = '<div class="content-wrapper">
        <div class="main-content">
            <div class="content-row">
                <div class="content-col">
                    <div class="campaign-data-wrap">
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="campaign-table-wrap">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Campaign</th>
                                                <th>Ad Group</th>
                                                <th>Ad</th>                                            
                                                <th>Impressions</th>
                                                <th>Reach</th>
                                                <th>Clicks</th>
                                                <th>CTR</th>
                                            </tr>
                                        </thead>
                                        <tbody>';



        foreach ($ads as $ad) {

            if ($param_campaign_id == $ad->campaign_id) {

                $content .= '<tr>';

                if ($ad->campaign_id != $prevCampaignId) {
                    $content .= '<td rowspan="' . $campaignColSpan[$ad->campaign_id] . '">' . $ad->campaign_name . '</td>';
                }

                if ($ad->adgroup_id != $prevAdgroupId) {
                    $content .= '<td rowspan="' . $adGroupColspan[$ad->adgroup_id] . '">' . $ad->adgroup_name . '</td>';
                }

                $content .= '<td rowspan="">' . $ad->adgroup_name . '</td>';

                $content .= '<td>' . $ad->ad_name . '</td>                                           
                                                <td>' . number_format($ad->total_impressions) . '</td>
                                                <td>' . number_format($ad->total_reach) . '</td>
                                                <td>' . number_format($ad->total_clicks) . '</td>
                                                <td>' . $ad->total_ctr . '%</td>
                                            </tr>';
            }


            if ($param_campaign_id == "") {

                $content .= '<tr>';

                if ($ad->campaign_id != $prevCampaignId) {
                    $content .= '<td rowspan="' . $campaignColSpan[$ad->campaign_id] . '">' . $ad->campaign_name . '</td>';
                }

                if ($ad->adgroup_id != $prevAdgroupId) {
                    $content .= '<td rowspan="' . $adGroupColspan[$ad->adgroup_id] . '">' . $ad->adgroup_name . '</td>';
                }

                $content .= '<td rowspan="">' . $ad->adgroup_name . '</td>
                                            <td>' . $ad->ad_name . '</td>                                           
                                            <td>' . number_format($ad->total_impressions) . '</td>
                                            <td>' . number_format($ad->total_reach) . '</td>
                                            <td>' . number_format($ad->total_clicks) . '</td>
                                            <td>' . $ad->total_ctr . '%</td>
                                        </tr>';
            }

            $prevCampaignId = $ad->campaign_id;
            $prevAdgroupId = $ad->adgroup_id;
        }
        $content .= '</tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
    
                    </div>
                </div>
            </div>
        </div>
    </div>';


        $section->addText($content);
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');

        try {
            $objWriter->save(storage_path('helloWorld.docx'));
        } catch (Exception $e) {
        }

        return response()->download(storage_path('helloWorld.docx'));
    }

    public function updateCampaignData(Request $request)
    {

        $result = ['status' => false, 'statusCode' => 401, 'message' => 'Server error!', 'data' => ''];

        $accessToken  = $request->header('accessToken');

        if (isset($accessToken)) {
            $userInfo = $this->userInfo;

            

            $username = $userInfo['username'];
            $type = $userInfo['type'];
            $advertiserArray = $userInfo['advertisers'];

            if ($type == 'Admins') {

                $fieldId = $request->fieldId;
                $fieldValue = $request->fieldValue;
                $campaignId = $request->campaignId;
                $advertiserId = $request->advertiserId;
                $fieldValue = str_replace(",", "", $fieldValue);
                $campaignData = [$fieldId => $fieldValue];
                Campaign::updateOrCreate(['campaign_id' => $campaignId, 'advertiser_id' => $advertiserId], $campaignData);

                $result = ['status' => true, 'statusCode' => 200, 'message' => '', 'data' => ''];
            }
        }
        return $result;
    }


    public function addReview(Request $request)
    {
        $accessToken  = $request->header('accessToken');
        if (isset($accessToken)) {
            $userInfo = $this->userInfo;

            

            $username = $userInfo['username'];
            $type = $userInfo['type'];
            $advertiserArray = $userInfo['advertisers'];

            if ($type == 'Admins') {
                $advertiserId = $request->advertiserId;
                $campaignId = $request->campaignId;
                $review = $request->review;
                $reviewTable = new Review();
                $reviewTable->advertiser_id = $advertiserId;
                $reviewTable->campaign_id = $campaignId;
                $reviewTable->review = $review;
                $reviewTable->save();

                echo json_encode(['status' => 1, 'message' => "Updated " . date('M d,Y')]);
                exit;
            }
        }
        echo json_encode(['status' => 0, 'message' => "Please try agian!"]);
        exit;
    }


    public function addUser(Request $request)
    {


        $accessToken  = $request->header('accessToken');
        $result = ['status' => false, 'message' => 'You do not have this permission.', 'data' => []];

        if (isset($accessToken)) {

            $userInfo = $this->userInfo;

            

            $username = $userInfo['username'];
            $type = $userInfo['type'];

            if ($type != 'Admins') {
                return response()->json($result);
            }

            $username = $request->username;
            $password = $request->password;
            $user_type = $request->user_type;
            $dashboard_access = $request->dashboard_access;

            $postRequest = array(
                "username" => $username,
                "password" => $password,
                "group" => $user_type
            );

            if (isset($dashboard_access) && count($dashboard_access) > 0 && $user_type == 'Users') {
                $postRequest['client'] = implode("|", $dashboard_access);
            }



            $postRequest = json_encode($postRequest);
            $authorization = "";
            $jsonArrayResponse = [];
            try {                

                $url = env('AWS_AUTH_URL');
                if (isset($accessToken)) {
                    $headers =  ['Authorization: ' . $accessToken, 'Content-Type: application/json'];
                }
                $cURLConnection = curl_init($url);

                curl_setopt($cURLConnection, CURLOPT_POST, 1);
                curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $postRequest);
                curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

                curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, $headers);
                $apiResponse = curl_exec($cURLConnection);

                $httpcode = curl_getinfo($cURLConnection, CURLINFO_HTTP_CODE);

                curl_close($cURLConnection);
                // $apiResponse - available data from the API request
                $jsonArrayResponse = json_decode($apiResponse);
                //var_dump($apiResponse);

            } catch (Exception $e) {
            }
            $msg = "";
            if ($httpcode == 403 || $httpcode == 400) {
                if(isset($jsonArrayResponse->errors))
                {
                    $msg = $jsonArrayResponse->errors->message[0];
                }
                else
                {
                    $msg = $jsonArrayResponse->message;
                }
            } else if ($httpcode == 200) {
                $msg = $jsonArrayResponse->message;
            } else {
                $msg = $jsonArrayResponse;
            }

            $result = ['status' => true, 'message' => $msg, 'statusCode' => $httpcode];
        }

        return response()->json($result);
    }

    /* Store advertiser image */
    public function uploadAdvertiserImage(Request $request)
    {
        $result = ['status' => false, 'statusCode' => 401, 'message' => 'Server error!', 'data' => ''];

        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $advertiserId = $request->advertiserId;
        $accessToken  = $request->header('accessToken');

        if (isset($accessToken)) {
            $userInfo = $this->userInfo;

            


            $username = $userInfo['username'];
            $type = $userInfo['type'];
            $advertiserArray = $userInfo['advertisers'];

            if ($type == 'Admins') {

                if ($request->file('file')) {
                    $path = Storage::disk('public_uploads')->putFile('', $request->file('file'));
                }

                $advertiserData = ['image' => $path];
                Advertiser::updateOrCreate(['advertiser_id' => $advertiserId], $advertiserData);
                $result = ['status' => true, 'statusCode' => 200, 'message' => 'Image successfully uploaded!', 'data' => ''];
            }
        }
        return $result;
    }

    public function logout(Request $request)
    {
        session_start();
        session_destroy();
        $request->session()->flush();
        setcookie('darkMode', "", time() - 3600);
        return redirect('/');
    }
}
