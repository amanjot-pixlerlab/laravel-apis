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
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AdvertisersController extends Controller
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

    public function authentication(Request $request)
    {
        if ($request->ajax()) {
            $userData  = json_decode($request->input('data'));

            //dd($userData->storage->);
            if (isset($userData) && count((array)$userData) > 0) {
                $username = $userData->username;
                $clientId = $userData->pool->clientId;
                $storage = (array)$userData->storage;
                $elementPrefix = 'CognitoIdentityServiceProvider.' . $clientId . '.' . $username;
                $accessToken = $storage[$elementPrefix . '.accessToken'];
                $refreshToken = $storage[$elementPrefix . '.refreshToken'];
                $userAttributes  = $storage[$elementPrefix . '.userData'];
                $userAttributes = json_decode($userAttributes);

                $tokenParts = explode(".", $accessToken);
                $tokenHeader = base64_decode($tokenParts[0]);
                $tokenPayload = base64_decode($tokenParts[1]);
                $jwtHeader = json_decode($tokenHeader);
                $jwtPayload = json_decode($tokenPayload);
                $payLoadInfo = (array)$jwtPayload;
                $advertisersList = [];
                $userType =  $payLoadInfo['cognito:groups']['0']; // ['Admins', 'Users']
                if($userType == 'Admins')
                {
                    $request->session()->put('is_admin', true);
                }
                else
                {
                    foreach($userAttributes->UserAttributes as $attributes)
                    {
                        if($attributes->Name == 'custom:clients')
                        {
                            $advertisersList = explode('|',$attributes->Value);
                        } 
                    }                    
                }
                $request->session()->put('advertisers', $advertisersList);

                $request->session()->put('userLogin', true);

                $result = ['status' => true, 'message' => 'User has been logged in successfully!'];
            }
            else
            {
                $result = ['status' => false, 'message' => 'Invalid Username or Password.'];
            }


            return response()->json($result);
        }
    }

    public function selectClient(Request $request)
    {
        $is_admin = $request->session()->get('is_admin');
        $advertiserArray= [];
        $advertisers = [];
        // If Auth code exists
        if ($request->get('auth_code')) {

            $auth_code = $request->get('auth_code');
            $this->setAccessToken($request, $auth_code);
            return redirect('select-client');
        } elseif ($request->session()->get('access_token')) {

            $method = "oauth2/advertiser/get";
            $app_id = env('TIKTOK_API_ID');
            $access_token = $request->session()->get('access_token');

            $secret = env('TIKTOK_SECRET_KEY');
            $params = ['access_token' => $access_token, 'app_id' => $app_id, 'secret' => $secret];
            $advertisers = $this->curlGetExecution($request, $method, $params);

            /* Save advertisers into database */
            $this->saveAdvertisers($advertisers, $access_token);
            /* Save advertisers into database */

            if ($advertisers->message == "OK") {
                foreach ($advertisers->data->list as $advertiser) {
                    $advertiserArray[] =  $advertiser->advertiser_id;
                }

                $advertisers = Advertiser::whereIn('advertiser_id', $advertiserArray)->get();
            } else {
                $advertisers = [];
            }
           
        } 
        else {
            
            $advertiserArray = $request->session()->get('advertisers');

            if($is_admin)
            {
                $advertisers = Advertiser::get();
               
            }
            else if(!$is_admin && isset($advertiserArray) && count($advertiserArray) > 0 )
            {
                $advertisers = Advertiser::whereIn('advertiser_id', $advertiserArray)->get();
                
            }
            else
            {
                return redirect('/');
            }
        }
        
        if($advertisers->count() == 1 )
        {
            foreach($advertisers as $advertiser)
            {
                return redirect()->route('dashboard', $advertiser['advertiser_id']);
            }            
        }

        echo "Advertisers created!";
        exit;
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

    public function setAccessToken(Request $request, $auth_code)
    {
        $postRequest = array(
            "secret" => env('TIKTOK_SECRET_KEY'),
            "app_id" => env('TIKTOK_API_ID'),
            "auth_code" => $auth_code
        );

        $cURLConnection = curl_init(env('TIKTOK_END_POINT') . 'oauth2/access_token/');
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

    }

    public function curlGetExecution(Request $request, $method, $params)
    {

        $access_token = $request->session()->get('access_token');

        $queryString = http_build_query($params);

        $url = env('TIKTOK_END_POINT') . $method . "/?" . $queryString;

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
}
