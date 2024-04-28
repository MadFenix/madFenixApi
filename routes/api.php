<?php
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
use Illuminate\Http\Request;
use App\Modules\User\Transformers\User as UserTransformer;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return new UserTransformer(auth()->user());
});

// Used to user authentication api

Route::namespace('\\App\\Modules\\User\\Infrastructure\\Controller\\')->group(function () {
    Route::post('/login', 'Api@login');
    Route::post('/deleteAccount', 'Api@deleteAccount');
    // TODO Route::post('/refreshToken', 'Api@refreshToken');
    Route::middleware('auth:sanctum')->post('/logout', 'Api@logout');
    Route::post('/forgotReset', 'Api@forgotReset');
    Route::post('/forgotSendResetLinkEmail', 'Api@forgotSendResetLinkEmail');
    Route::post('/register', 'Api@register');
    Route::post('/verify', 'Api@verify');
});

Route::namespace('\\App\\Modules\\Game\\Ranking\\Infrastructure\\Controller')->group(function () {
    Route::get('ranking/getClassification', 'Api@getClassification');
    Route::get('ranking/getGameStarted', 'Api@getGameStarted');

    Route::get('ranking/getSeasonClassificationPerTime', 'ApiSeason@getClassificationPerTime');
    Route::get('ranking/getSeasonClassificationPerPoints', 'ApiSeason@getClassificationPerPoints');
    Route::get('ranking/getSeasonGameStarted', 'ApiSeason@getGameStarted');
});

Route::namespace('\\App\\Modules\\Twitch\\Infrastructure\\Controller')->group(function () {
    Route::get('twitch/connectAccount', 'Api@connectTwitchAccount');
});

Route::namespace('\\App\\Modules\\Steam\\Infrastructure\\Controller')->group(function () {
    Route::get('steam/connectAccount', 'Api@connectSteamAccount');
});

Route::namespace('\\App\\Modules\\Store\\Infrastructure\\Controller')->group(function () {
    Route::get('store/details', 'Api@getStoreDetails');
    Route::get('store/validateProductOrder', 'Api@validateProductOrder');
});

/*Route::namespace('\\App\\Modules\\Assistant\\Thred\\Infrastructure\\Controller')->group(function () {
    Route::get('assistant/thred/getResponseFromAI', 'Api@getResponseFromAI');
    Route::post('assistant/thred/getAudioResponseFromAI', 'Api@getAudioResponseFromAI');
    Route::post('assistant/thred/getAudioResponseFromMixAI', 'Api@getAudioResponseFromMixAI');
    Route::post('assistant/thred/getClientCommandFromMixAI', 'Api@getClientCommandFromMixAI');
});*/

// Usual routes authed
Route::namespace('\\App\\Modules\\')->middleware('auth:sanctum')->group(function () {
    Route::namespace('User\\Infrastructure\\Controller')->group(function () {
        Route::post('linkWallet', 'Api@linkWallet');
        Route::post('setIP', 'Api@setIP');
        Route::post('getIP', 'Api@getIP');
    });
    Route::namespace('Game\\Ranking\\Infrastructure\\Controller')->group(function () {
        Route::post('ranking/addRanking', 'Api@addRanking');
        Route::get('ranking/getRanking', 'Api@getRanking');
        Route::post('ranking/addSeasonRanking', 'ApiSeason@addRanking');
        Route::get('ranking/getSeasonRankingPerTime', 'ApiSeason@getRankingPerTime');
        Route::get('ranking/getSeasonRankingPerPoints', 'ApiSeason@getRankingPerPoints');
        Route::get('ranking/getSeasonUserClassificationPerTime', 'ApiSeason@getUserClassificationPerTime');
        Route::get('ranking/getSeasonUserClassificationPerPoints', 'ApiSeason@getUserClassificationPerPoints');
    });
    Route::namespace('Game\\Profile\\Infrastructure\\Controller')->group(function () {
        Route::post('profile/addPlumas', 'Api@addPluma');
        Route::post('profile/addOro', 'Api@addOro');
        Route::post('profile/getUserProfile', 'Api@getUserProfile');
        Route::post('profile/subtractPlumaUser', 'Api@subtractPlumaUser');
        Route::post('profile/subtractOroUser', 'Api@subtractOroUser');
    });
    Route::namespace('Game\\Coupon\\Infrastructure\\Controller')->group(function () {
        Route::post('coupon/usePlumasCoupon', 'Api@usePlumasCoupon');
        Route::post('coupon/useOroCoupon', 'Api@useOroCoupon');
    });
    Route::namespace('Blockchain\\Wallet\\Infrastructure\\Controller')->group(function () {
        Route::post('wallet/transferZen', 'Api@transferZen');
        Route::get('wallet/show', 'Api@show');
    });
    Route::namespace('Blockchain\\Block\\Infrastructure\\Controller')->group(function () {
        Route::post('blockchain/transferPlumasToHedera', 'Api@transferPlumasToHedera');
        Route::post('blockchain/transferOroToHedera', 'Api@transferOroToHedera');
        Route::post('blockchain/transferNftToHedera', 'Api@transferNftToHedera');
    });
    Route::namespace('Game\\Season\\Infrastructure\\Controller')->group(function () {
        Route::get('season/seasonDetails', 'Api@seasonDetails');
        Route::post('season/redeemSeasonLvl', 'Api@redeemSeasonLvl');
    });

    Route::namespace('Twitch\\Infrastructure\\Controller')->group(function () {
        Route::post('twitch/disconnectTwitch', 'Api@disconnectTwitch');
    });

    Route::namespace('Steam\\Infrastructure\\Controller')->group(function () {
        Route::post('steam/disconnectSteam', 'Api@disconnectSteam');
    });

    Route::namespace('Store\\Infrastructure\\Controller')->group(function () {
        Route::post('store/addProductToOrder', 'Api@addProductToOrder');
    });
});

// Public game routes
Route::namespace('\\App\\Modules\\Blockchain\\')->group(function () {
    // Route::get('sorteo', 'Wallet\\Infrastructure\\Controller\\Api@sorteo');
});
