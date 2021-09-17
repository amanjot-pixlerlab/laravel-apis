<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AdController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/authentication', [AdController::class, 'authentication'])->name('authentication');

Route::get('/clients', [AdController::class, 'clients'])->name('clients');

Route::get('/campaigns', [AdController::class, 'getCampaigns'])->name('campaigns');

Route::post('/upload-advertiser-image', [AdController::class,'uploadAdvertiserImage'])->name('upload.advertiser.image'); 

Route::get('/campaign-reach-data/', [AdController::class,'campaignReachData'])->name('campaign.reach.data');

Route::post('/update-campaign-data', [AdController::class,'updateCampaignData'])->name('updatecampaigndata');

Route::post('/add-review', [AdController::class,'addReview'])->name('addreview');

Route::post('/add-user', [AdController::class,'addUser'])->name('adduser');

Route::get('/get-csv', [AdController::class,'getCsv'])->name('get.csv');