<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


use App\Http\Controllers\AdvertisersController;
use App\Http\Controllers\CronsController;

Route::get('tiktok-login', [AdvertisersController::class,'index']);

Route::post('/authentication', [AdvertisersController::class,'authentication'])->name('authentication');

Route::get('/select-client', [AdvertisersController::class,'selectClient'])->name('selectclient');

Route::get('/campaign-test', [CronsController::class,'campaignTest']); 

require __DIR__.'/auth.php';