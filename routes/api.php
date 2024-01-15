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

Route::namespace('\\App\\Modules\\Assistant\\Thred\\Infrastructure\\Controller')->group(function () {
    Route::get('assistant/thred/getResponseFromAI', 'Api@getResponseFromAI');
    Route::post('assistant/thred/getAudioResponseFromAI', 'Api@getAudioResponseFromAI');
    Route::post('assistant/thred/getAudioResponseFromMixAI', 'Api@getAudioResponseFromMixAI');
    Route::post('assistant/thred/getClientCommandFromMixAI', 'Api@getClientCommandFromMixAI');
});

Route::namespace('\\App\\Modules\\Game\\State\\Infrastructure\\Controller')->group(function () {
    Route::get('game/state/getState', 'Api@getState');
});

// Usual routes authed
Route::namespace('\\App\\Modules\\')->middleware('auth:sanctum')->group(function () {
    Route::namespace('User\\Infrastructure\\Controller')->group(function () {
        Route::get('listUsers', 'Api@index');
        Route::post('linkWallet', 'Api@linkWallet');
        Route::post('setIP', 'Api@setIP');
        Route::post('getIP', 'Api@getIP');
    });
    Route::namespace('Event\\Infrastructure\\Controller')->group(function () {
        Route::get('eventSummary', 'Api@eventSummary');
        Route::resource('event', 'Api');
    });
    Route::namespace('Game\\Node\\Infrastructure\\Controller')->group(function () {
        Route::resource('node', 'Api');
    });
    Route::namespace('Game\\Blueprint\\Infrastructure\\Controller')->group(function () {
        Route::resource('blueprint', 'Api');
        Route::post('addBlueprintToCraftUser', 'Api@addBlueprintToCraftUser');
        Route::get('getBlueprintToCraftUser', 'Api@getBlueprintToCraftUser');
    });
    Route::namespace('Game\\Plane\\Infrastructure\\Controller')->group(function () {
        Route::resource('plane', 'Api');
    });
    Route::namespace('Game\\Profile\\Infrastructure\\Controller')->group(function () {
        Route::resource('profile', 'Api');
    });
    Route::namespace('Game\\Ranking\\Infrastructure\\Controller')->group(function () {
        Route::post('ranking/addRanking', 'Api@addRanking');
        Route::get('ranking/getRanking', 'Api@getRanking');
        Route::get('ranking/getSeasonUserClassificationPerTime', 'ApiSeason@getUserClassificationPerTime');
        Route::get('ranking/getSeasonUserClassificationPerPoints', 'ApiSeason@getUserClassificationPerPoints');
    });
    Route::namespace('Blockchain\\Wallet\\Infrastructure\\Controller')->group(function () {
        Route::post('wallet/transferZen', 'Api@transferZen');
        Route::get('wallet/show', 'Api@show');
    });
});

// Public game routes
Route::namespace('\\App\\Modules\\Game\\')->group(function () {
    Route::get('material', 'Node\\Infrastructure\\Controller\\Api@nodeSummary');
});
Route::namespace('\\App\\Modules\\Blockchain\\')->group(function () {
    Route::get('sorteo', 'Wallet\\Infrastructure\\Controller\\Api@sorteo');
});
