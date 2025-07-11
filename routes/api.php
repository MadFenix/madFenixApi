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

Route::prefix('/{account}')->group(function () {
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return new UserTransformer(auth()->user());
    });

    // Used to user authentication api

    Route::namespace('\\App\\Modules\\User\\Infrastructure\\Controller\\')->group(function () {
        Route::post('/login', 'Api@login');
        Route::post('/verify2fa', 'Api@verify2fa');
        Route::post('/deleteAccount', 'Api@deleteAccount');
        // TODO Route::post('/refreshToken', 'Api@refreshToken');
        Route::middleware('auth:sanctum')->post('/logout', 'Api@logout');
        Route::post('/forgotReset', 'Api@forgotReset');
        Route::post('/forgotSendResetLinkEmail', 'Api@forgotSendResetLinkEmail');
        Route::post('/register', 'Api@register');
        Route::post('/verify', 'Api@verify');
    });

    // Public theme routes
    Route::namespace('\\App\\Modules\\Theme\\Infrastructure\\Controller')->group(function () {
        Route::get('theme/active', 'Api@getActiveTheme');
    });

    // Public page routes
    Route::namespace('\\App\\Modules\\Page\\Infrastructure\\Controller')->group(function () {
        Route::get('page/{name}/get', 'Api@getByName');
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
        Route::post('store/validateProductOrder', 'Api@validateProductOrder');
    });

    // Usual routes authed
    Route::namespace('\\App\\Modules\\')->middleware('auth:sanctum')->group(function () {
        Route::prefix('/manager')->group(function () {
            Route::middleware('manager')->group(function () {
                Route::get('/admin-dashboard', 'Game\\Profile\\Infrastructure\\Controller\\Api@adminDashboard');

                Route::apiResource('poll', 'Game\\Poll\\Infrastructure\\Controller\\Api')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/poll', 'Game\\Poll\\Infrastructure\\Controller\\Api@destroy');
                Route::get('/poll-download', 'Game\\Poll\\Infrastructure\\Controller\\Api@download');
                Route::get('/poll-fields', 'Game\\Poll\\Infrastructure\\Controller\\Api@fields');
                Route::post('/poll-upload', 'Game\\Poll\\Infrastructure\\Controller\\Api@upload');
                Route::get('/poll-upload', 'Game\\Poll\\Infrastructure\\Controller\\Api@uploadStatus');
                Route::delete('/poll-upload/{id}', 'Game\\Poll\\Infrastructure\\Controller\\Api@deleteUpload');

                Route::apiResource('coupon-free', 'Game\\Coupon\\Infrastructure\\Controller\\Api')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/coupon-free', 'Game\\Coupon\\Infrastructure\\Controller\\Api@destroy');
                Route::get('/coupon-free-download', 'Game\\Coupon\\Infrastructure\\Controller\\Api@download');
                Route::get('/coupon-free-fields', 'Game\\Coupon\\Infrastructure\\Controller\\Api@fields');
                Route::post('/coupon-free-upload', 'Game\\Coupon\\Infrastructure\\Controller\\Api@upload');
                Route::get('/coupon-free-upload', 'Game\\Coupon\\Infrastructure\\Controller\\Api@uploadStatus');
                Route::delete('/coupon-free-upload/{id}', 'Game\\Coupon\\Infrastructure\\Controller\\Api@deleteUpload');
                Route::apiResource('coupon-payment', 'Game\\Coupon\\Infrastructure\\Controller\\ApiOro')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/coupon-payment', 'Game\\Coupon\\Infrastructure\\Controller\\ApiOro@destroy');
                Route::get('/coupon-payment-download', 'Game\\Coupon\\Infrastructure\\Controller\\ApiOro@download');
                Route::get('/coupon-payment-fields', 'Game\\Coupon\\Infrastructure\\Controller\\ApiOro@fields');
                Route::post('/coupon-payment-upload', 'Game\\Coupon\\Infrastructure\\Controller\\ApiOro@upload');
                Route::get('/coupon-payment-upload', 'Game\\Coupon\\Infrastructure\\Controller\\ApiOro@uploadStatus');
                Route::delete('/coupon-payment-upload/{id}', 'Game\\Coupon\\Infrastructure\\Controller\\ApiOro@deleteUpload');
                Route::apiResource('coupon-items', 'Game\\Coupon\\Infrastructure\\Controller\\ApiItem')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/coupon-items', 'Game\\Coupon\\Infrastructure\\Controller\\ApiItem@destroy');
                Route::get('/coupon-items-download', 'Game\\Coupon\\Infrastructure\\Controller\\ApiItem@download');
                Route::get('/coupon-items-fields', 'Game\\Coupon\\Infrastructure\\Controller\\ApiItem@fields');
                Route::post('/coupon-items-upload', 'Game\\Coupon\\Infrastructure\\Controller\\ApiItem@upload');
                Route::get('/coupon-items-upload', 'Game\\Coupon\\Infrastructure\\Controller\\ApiItem@uploadStatus');
                Route::delete('/coupon-items-upload/{id}', 'Game\\Coupon\\Infrastructure\\Controller\\ApiItem@deleteUpload');

                Route::apiResource('nft', 'Blockchain\\Block\\Infrastructure\\Controller\\ApiNft')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/nft', 'Blockchain\\Block\\Infrastructure\\Controller\\ApiNft@destroy');
                Route::get('/nft-download', 'Blockchain\\Block\\Infrastructure\\Controller\\ApiNft@download');
                Route::get('/nft-fields', 'Blockchain\\Block\\Infrastructure\\Controller\\ApiNft@fields');
                Route::post('/nft-upload', 'Blockchain\\Block\\Infrastructure\\Controller\\ApiNft@upload');
                Route::get('/nft-upload', 'Blockchain\\Block\\Infrastructure\\Controller\\ApiNft@uploadStatus');
                Route::delete('/nft-upload/{id}', 'Blockchain\\Block\\Infrastructure\\Controller\\ApiNft@deleteUpload');

                Route::apiResource('nft-identification', 'Blockchain\\Block\\Infrastructure\\Controller\\ApiNftIdentification')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/nft-identification', 'Blockchain\\Block\\Infrastructure\\Controller\\ApiNftIdentification@destroy');
                Route::get('/nft-identification-download', 'Blockchain\\Block\\Infrastructure\\Controller\\ApiNftIdentification@download');
                Route::get('/nft-identification-fields', 'Blockchain\\Block\\Infrastructure\\Controller\\ApiNftIdentification@fields');
                Route::post('/nft-identification-upload', 'Blockchain\\Block\\Infrastructure\\Controller\\ApiNftIdentification@upload');
                Route::get('/nft-identification-upload', 'Blockchain\\Block\\Infrastructure\\Controller\\ApiNftIdentification@uploadStatus');
                Route::delete('/nft-identification-upload/{id}', 'Blockchain\\Block\\Infrastructure\\Controller\\ApiNftIdentification@deleteUpload');

                Route::apiResource('season', 'Game\\Season\\Infrastructure\\Controller\\Api')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/season', 'Game\\Season\\Infrastructure\\Controller\\Api@destroy');
                Route::get('/season-download', 'Game\\Season\\Infrastructure\\Controller\\Api@download');
                Route::get('/season-fields', 'Game\\Season\\Infrastructure\\Controller\\Api@fields');
                Route::post('/season-upload', 'Game\\Season\\Infrastructure\\Controller\\Api@upload');
                Route::get('/season-upload', 'Game\\Season\\Infrastructure\\Controller\\Api@uploadStatus');
                Route::delete('/season-upload/{id}', 'Game\\Season\\Infrastructure\\Controller\\Api@deleteUpload');

                Route::apiResource('season-reward', 'Game\\Season\\Infrastructure\\Controller\\ApiReward')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/season-reward', 'Game\\Season\\Infrastructure\\Controller\\ApiReward@destroy');
                Route::get('/season-reward-download', 'Game\\Season\\Infrastructure\\Controller\\ApiReward@download');
                Route::get('/season-reward-fields', 'Game\\Season\\Infrastructure\\Controller\\ApiReward@fields');
                Route::post('/season-reward-upload', 'Game\\Season\\Infrastructure\\Controller\\ApiReward@upload');
                Route::get('/season-reward-upload', 'Game\\Season\\Infrastructure\\Controller\\ApiReward@uploadStatus');
                Route::delete('/season-reward-upload/{id}', 'Game\\Season\\Infrastructure\\Controller\\ApiReward@deleteUpload');

                Route::apiResource('event-metas', 'Event\\Infrastructure\\Controller\\ApiMeta')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/event-metas', 'Event\\Infrastructure\\Controller\\ApiMeta@destroy');
                Route::get('/event-metas-download', 'Event\\Infrastructure\\Controller\\ApiMeta@download');
                Route::get('/event-metas-fields', 'Event\\Infrastructure\\Controller\\ApiMeta@fields');
                Route::post('/event-metas-upload', 'Event\\Infrastructure\\Controller\\ApiMeta@upload');
                Route::get('/event-metas-upload', 'Event\\Infrastructure\\Controller\\ApiMeta@uploadStatus');
                Route::delete('/event-metas-upload/{id}', 'Event\\Infrastructure\\Controller\\ApiMeta@deleteUpload');

                Route::apiResource('product', 'Store\\Infrastructure\\Controller\\Api')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/product', 'Store\\Infrastructure\\Controller\\Api@destroy');
                Route::get('/product-download', 'Store\\Infrastructure\\Controller\\Api@download');
                Route::get('/product-fields', 'Store\\Infrastructure\\Controller\\Api@fields');
                Route::post('/product-upload', 'Store\\Infrastructure\\Controller\\Api@upload');
                Route::get('/product-upload', 'Store\\Infrastructure\\Controller\\Api@uploadStatus');
                Route::delete('/product-upload/{id}', 'Store\\Infrastructure\\Controller\\Api@deleteUpload');

                Route::apiResource('product-order', 'Store\\Infrastructure\\Controller\\ApiOrder')->only(['index', 'destroy']);
                Route::delete('/product-order', 'Store\\Infrastructure\\Controller\\ApiOrder@destroy');
                Route::get('/product-order-download', 'Store\\Infrastructure\\Controller\\ApiOrder@download');

                Route::apiResource('user', 'User\\Infrastructure\\Controller\\ApiManager')->only(['index', 'show', 'update']);
                Route::get('/user-download', 'User\\Infrastructure\\Controller\\ApiManager@download');
                Route::post('/user-password-reset', 'User\\Infrastructure\\Controller\\Api@resetPasswordLogged');
                Route::get('/user-generate2fa', 'User\\Infrastructure\\Controller\\Api@generate2fa');
                Route::post('/user-confirm2fa', 'User\\Infrastructure\\Controller\\Api@confirm2fa');

                Route::apiResource('profile', 'Game\\Profile\\Infrastructure\\Controller\\Api')->only(['index', 'show', 'update']);
                Route::get('/profile-download', 'Game\\Profile\\Infrastructure\\Controller\\Api@download');

                Route::apiResource('theme', 'Theme\\Infrastructure\\Controller\\Api')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/theme', 'Theme\\Infrastructure\\Controller\\Api@destroy');
                Route::get('/theme-download', 'Theme\\Infrastructure\\Controller\\Api@download');
                Route::get('/theme-fields', 'Theme\\Infrastructure\\Controller\\Api@fields');
                Route::post('/theme-upload', 'Theme\\Infrastructure\\Controller\\Api@upload');
                Route::get('/theme-upload', 'Theme\\Infrastructure\\Controller\\Api@uploadStatus');
                Route::delete('/theme-upload/{id}', 'Theme\\Infrastructure\\Controller\\Api@deleteUpload');
                Route::post('/theme/{id}/activate', 'Theme\\Infrastructure\\Controller\\Api@activate');

                Route::apiResource('theme-config', 'Theme\\Infrastructure\\Controller\\ApiConfig')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/theme-config', 'Theme\\Infrastructure\\Controller\\ApiConfig@destroy');
                Route::get('/theme-config-download', 'Theme\\Infrastructure\\Controller\\ApiConfig@download');
                Route::get('/theme-config-fields', 'Theme\\Infrastructure\\Controller\\ApiConfig@fields');
                Route::post('/theme-config-upload', 'Theme\\Infrastructure\\Controller\\ApiConfig@upload');
                Route::get('/theme-config-upload', 'Theme\\Infrastructure\\Controller\\ApiConfig@uploadStatus');
                Route::delete('/theme-config-upload/{id}', 'Theme\\Infrastructure\\Controller\\ApiConfig@deleteUpload');
                Route::post('/theme-config/{id}/activate', 'Theme\\Infrastructure\\Controller\\ApiConfig@activate');

                Route::apiResource('page', 'Page\\Infrastructure\\Controller\\Api')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/page', 'Page\\Infrastructure\\Controller\\Api@destroy');
                Route::get('/page-download', 'Page\\Infrastructure\\Controller\\Api@download');
                Route::get('/page-fields', 'Page\\Infrastructure\\Controller\\Api@fields');
                Route::post('/page-upload', 'Page\\Infrastructure\\Controller\\Api@upload');
                Route::get('/page-upload', 'Page\\Infrastructure\\Controller\\Api@uploadStatus');
                Route::delete('/page-upload/{id}', 'Page\\Infrastructure\\Controller\\Api@deleteUpload');
            });
            Route::middleware('employee')->group(function () {
                // TODO endpoints to ambassadors
            });
        });

        Route::namespace('EmployeeManager\\Infrastructure\\Controller')->group(function () {
            Route::get('/user-is-manager', 'EmployeeController@userIsManager')->name('user-is-manager');
            Route::get('/user-is-employee', 'EmployeeController@userIsEmployee')->name('user-is-employee');
        });

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
            Route::post('profile/setAvatar', 'Api@setAvatar');
            Route::post('profile/setEstado', 'Api@setEstado');
            Route::post('profile/subtractPlumaUser', 'Api@subtractPlumaUser');
            Route::post('profile/subtractOroUser', 'Api@subtractOroUser');
            Route::post('profile/setUserProfileReferredCode', 'Api@setUserProfileReferredCode');
            Route::post('profile/setUserProfileReferredCodeFrom', 'Api@setUserProfileReferredCodeFrom');
            Route::post('profile/setUserProfileHederaWalletCheck', 'Api@setUserProfileHederaWalletCheck');
        });
        Route::namespace('Event\\Infrastructure\\Controller')->group(function () {
            Route::get('event/list', 'Api@index');
            Route::post('event/readEvent', 'Api@readEvent');
        });
        Route::namespace('Game\\Fighter\\Infrastructure\\Controller')->group(function () {
            Route::get('fighter/getFighterUser', 'Api@getFighterUser');
            Route::post('fighter/setFighterUserDecks', 'Api@setFighterUserDecks');
            Route::get('fighter/getFighterFriends', 'Api@getFighterFriends');
            Route::post('fighter/findFighterFriend', 'Api@findFighterFriend');
            Route::post('fighter/requestFighterFriend', 'Api@requestFighterFriend');
            Route::get('fighter/getFighterFriendRequests', 'Api@getFighterFriendRequests');
            Route::post('fighter/approveFighterFriendRequest', 'Api@approveFighterFriendRequest');
            Route::post('fighter/cancelFighterFriendRequest', 'Api@cancelFighterFriendRequest');
            Route::get('fighter/getRanking', 'Api@getRanking');
            Route::post('fighter/findFighterUserBattle', 'Api@findFighterUserBattle');
            Route::post('fighter/saveFighterUserBattleTurn', 'Api@saveFighterUserBattleTurn');
            Route::post('fighter/resolveFighterUsersBattleTurn', 'Api@resolveFighterUsersBattleTurn');
            Route::get('fighter/getFighterUserBattle', 'Api@getFighterUserBattle');
        });
        Route::namespace('Game\\Coupon\\Infrastructure\\Controller')->group(function () {
            Route::post('coupon/usePlumasCoupon', 'Api@usePlumasCoupon');
            Route::post('coupon/useOroCoupon', 'Api@useOroCoupon');
            Route::post('coupon/useItemCoupon', 'Api@useItemCoupon');
            Route::post('coupon/useCoupon', 'Api@useCoupon');
        });
        Route::namespace('Game\\Poll\\Infrastructure\\Controller')->group(function () {
            Route::get('poll/pollDetails', 'Api@pollDetails');
            Route::get('poll/pollsDetailsLast30Days', 'Api@pollsDetailsLast30Days');
            Route::post('poll/answerPoll', 'Api@answerPoll');
        });
        Route::namespace('Game\\ThePhoenixDiary\\Infrastructure\\Controller')->group(function () {
            Route::get('thePhoenixDiary/getCharacters', 'Api@getCharacters');
            Route::post('thePhoenixDiary/createNewGame', 'Api@createNewGame');
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
            Route::get('store/details', 'Api@getStoreDetails');
            Route::post('store/addProductToOrder', 'Api@addProductToOrder');
            Route::post('store/addEventGiftToOrder', 'Api@addEventGiftToOrder');
            Route::get('store/getLastProductOrders', 'Api@getLastProductOrders');
        });

        Route::namespace('Habit\\Infrastructure\\Controller')->group(function () {
            Route::post('habit/post', 'Api@postHabit');
            Route::post('habit/postComplete', 'Api@postHabitComplete');
        });
    });

    // Public game routes
    Route::namespace('\\App\\Modules\\Blockchain\\')->group(function () {
        // Route::get('sorteo', 'Wallet\\Infrastructure\\Controller\\Api@sorteo');
    });

    Route::namespace('\\App\\Modules\\Blockchain\\Nft\\Infrastructure\\Controller')->group(function () {
        Route::get('nft/signMessage', 'Api@getSignMessage');
        Route::post('nft/getWalletCollectionTokens', 'Api@getOwnedTokens');
    });
});
