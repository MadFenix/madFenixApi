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

Route::namespace('\\App\\Modules\\Store\\Infrastructure\\Controller')->group(function () {
    Route::post('store/validateProductOrder', 'Api@validateProductOrder');
});

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

    Route::namespace('\\App\\Modules\\Store\\Infrastructure\\Controller')->group(function () {
        Route::get('store/generateStripeLink', 'Api@generateStripeLink');
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

                Route::apiResource('character', 'Universe\\Character\\Infrastructure\\Controller\\Api')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/character', 'Universe\\Character\\Infrastructure\\Controller\\Api@destroy');
                Route::get('/character-download', 'Universe\\Character\\Infrastructure\\Controller\\Api@download');
                Route::get('/character-fields', 'Universe\\Character\\Infrastructure\\Controller\\Api@fields');
                Route::post('/character-upload', 'Universe\\Character\\Infrastructure\\Controller\\Api@upload');
                Route::get('/character-upload', 'Universe\\Character\\Infrastructure\\Controller\\Api@uploadStatus');
                Route::delete('/character-upload/{id}', 'Universe\\Character\\Infrastructure\\Controller\\Api@deleteUpload');

                Route::apiResource('character-ability', 'Universe\\Character\\Infrastructure\\Controller\\ApiAbility')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/character-ability', 'Universe\\Character\\Infrastructure\\Controller\\ApiAbility@destroy');
                Route::get('/character-ability-download', 'Universe\\Character\\Infrastructure\\Controller\\ApiAbility@download');
                Route::get('/character-ability-fields', 'Universe\\Character\\Infrastructure\\Controller\\ApiAbility@fields');
                Route::post('/character-ability-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiAbility@upload');
                Route::get('/character-ability-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiAbility@uploadStatus');
                Route::delete('/character-ability-upload/{id}', 'Universe\\Character\\Infrastructure\\Controller\\ApiAbility@deleteUpload');

                Route::apiResource('character-action-result', 'Universe\\Character\\Infrastructure\\Controller\\ApiActionResult')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/character-action-result', 'Universe\\Character\\Infrastructure\\Controller\\ApiActionResult@destroy');
                Route::get('/character-action-result-download', 'Universe\\Character\\Infrastructure\\Controller\\ApiActionResult@download');
                Route::get('/character-action-result-fields', 'Universe\\Character\\Infrastructure\\Controller\\ApiActionResult@fields');
                Route::post('/character-action-result-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiActionResult@upload');
                Route::get('/character-action-result-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiActionResult@uploadStatus');
                Route::delete('/character-action-result-upload/{id}', 'Universe\\Character\\Infrastructure\\Controller\\ApiActionResult@deleteUpload');

                Route::apiResource('character-archetype', 'Universe\\Character\\Infrastructure\\Controller\\ApiArchetype')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/character-archetype', 'Universe\\Character\\Infrastructure\\Controller\\ApiArchetype@destroy');
                Route::get('/character-archetype-download', 'Universe\\Character\\Infrastructure\\Controller\\ApiArchetype@download');
                Route::get('/character-archetype-fields', 'Universe\\Character\\Infrastructure\\Controller\\ApiArchetype@fields');
                Route::post('/character-archetype-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiArchetype@upload');
                Route::get('/character-archetype-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiArchetype@uploadStatus');
                Route::delete('/character-archetype-upload/{id}', 'Universe\\Character\\Infrastructure\\Controller\\ApiArchetype@deleteUpload');

                Route::apiResource('character-category', 'Universe\\Character\\Infrastructure\\Controller\\ApiCategory')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/character-category', 'Universe\\Character\\Infrastructure\\Controller\\ApiCategory@destroy');
                Route::get('/character-category-download', 'Universe\\Character\\Infrastructure\\Controller\\ApiCategory@download');
                Route::get('/character-category-fields', 'Universe\\Character\\Infrastructure\\Controller\\ApiCategory@fields');
                Route::post('/character-category-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiCategory@upload');
                Route::get('/character-category-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiCategory@uploadStatus');
                Route::delete('/character-category-upload/{id}', 'Universe\\Character\\Infrastructure\\Controller\\ApiCategory@deleteUpload');

                Route::apiResource('character-category-field', 'Universe\\Character\\Infrastructure\\Controller\\ApiCategoryField')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/character-category-field', 'Universe\\Character\\Infrastructure\\Controller\\ApiCategoryField@destroy');
                Route::get('/character-category-field-download', 'Universe\\Character\\Infrastructure\\Controller\\ApiCategoryField@download');
                Route::get('/character-category-field-fields', 'Universe\\Character\\Infrastructure\\Controller\\ApiCategoryField@fields');
                Route::post('/character-category-field-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiCategoryField@upload');
                Route::get('/character-category-field-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiCategoryField@uploadStatus');
                Route::delete('/character-category-field-upload/{id}', 'Universe\\Character\\Infrastructure\\Controller\\ApiCategoryField@deleteUpload');

                Route::apiResource('character-event', 'Universe\\Character\\Infrastructure\\Controller\\ApiCharacterEvent')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/character-event', 'Universe\\Character\\Infrastructure\\Controller\\ApiCharacterEvent@destroy');
                Route::get('/character-event-download', 'Universe\\Character\\Infrastructure\\Controller\\ApiCharacterEvent@download');
                Route::get('/character-event-fields', 'Universe\\Character\\Infrastructure\\Controller\\ApiCharacterEvent@fields');
                Route::post('/character-event-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiCharacterEvent@upload');
                Route::get('/character-event-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiCharacterEvent@uploadStatus');
                Route::delete('/character-event-upload/{id}', 'Universe\\Character\\Infrastructure\\Controller\\ApiCharacterEvent@deleteUpload');

                Route::apiResource('character-media', 'Universe\\Character\\Infrastructure\\Controller\\ApiCharacterMedia')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/character-media', 'Universe\\Character\\Infrastructure\\Controller\\ApiCharacterMedia@destroy');
                Route::get('/character-media-download', 'Universe\\Character\\Infrastructure\\Controller\\ApiCharacterMedia@download');
                Route::get('/character-media-fields', 'Universe\\Character\\Infrastructure\\Controller\\ApiCharacterMedia@fields');
                Route::post('/character-media-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiCharacterMedia@upload');
                Route::get('/character-media-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiCharacterMedia@uploadStatus');
                Route::delete('/character-media-upload/{id}', 'Universe\\Character\\Infrastructure\\Controller\\ApiCharacterMedia@deleteUpload');

                Route::apiResource('character-clone-prefix', 'Universe\\Character\\Infrastructure\\Controller\\ApiClonePrefix')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/character-clone-prefix', 'Universe\\Character\\Infrastructure\\Controller\\ApiClonePrefix@destroy');
                Route::get('/character-clone-prefix-download', 'Universe\\Character\\Infrastructure\\Controller\\ApiClonePrefix@download');
                Route::get('/character-clone-prefix-fields', 'Universe\\Character\\Infrastructure\\Controller\\ApiClonePrefix@fields');
                Route::post('/character-clone-prefix-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiClonePrefix@upload');
                Route::get('/character-clone-prefix-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiClonePrefix@uploadStatus');
                Route::delete('/character-clone-prefix-upload/{id}', 'Universe\\Character\\Infrastructure\\Controller\\ApiClonePrefix@deleteUpload');

                Route::apiResource('character-expression', 'Universe\\Character\\Infrastructure\\Controller\\ApiExpression')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/character-expression', 'Universe\\Character\\Infrastructure\\Controller\\ApiExpression@destroy');
                Route::get('/character-expression-download', 'Universe\\Character\\Infrastructure\\Controller\\ApiExpression@download');
                Route::get('/character-expression-fields', 'Universe\\Character\\Infrastructure\\Controller\\ApiExpression@fields');
                Route::post('/character-expression-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiExpression@upload');
                Route::get('/character-expression-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiExpression@uploadStatus');
                Route::delete('/character-expression-upload/{id}', 'Universe\\Character\\Infrastructure\\Controller\\ApiExpression@deleteUpload');

                Route::apiResource('character-hierarchy', 'Universe\\Character\\Infrastructure\\Controller\\ApiHierarchy')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/character-hierarchy', 'Universe\\Character\\Infrastructure\\Controller\\ApiHierarchy@destroy');
                Route::get('/character-hierarchy-download', 'Universe\\Character\\Infrastructure\\Controller\\ApiHierarchy@download');
                Route::get('/character-hierarchy-fields', 'Universe\\Character\\Infrastructure\\Controller\\ApiHierarchy@fields');
                Route::post('/character-hierarchy-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiHierarchy@upload');
                Route::get('/character-hierarchy-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiHierarchy@uploadStatus');
                Route::delete('/character-hierarchy-upload/{id}', 'Universe\\Character\\Infrastructure\\Controller\\ApiHierarchy@deleteUpload');

                Route::apiResource('character-rank', 'Universe\\Character\\Infrastructure\\Controller\\ApiRank')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/character-rank', 'Universe\\Character\\Infrastructure\\Controller\\ApiRank@destroy');
                Route::get('/character-rank-download', 'Universe\\Character\\Infrastructure\\Controller\\ApiRank@download');
                Route::get('/character-rank-fields', 'Universe\\Character\\Infrastructure\\Controller\\ApiRank@fields');
                Route::post('/character-rank-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiRank@upload');
                Route::get('/character-rank-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiRank@uploadStatus');
                Route::delete('/character-rank-upload/{id}', 'Universe\\Character\\Infrastructure\\Controller\\ApiRank@deleteUpload');

                Route::apiResource('character-role', 'Universe\\Character\\Infrastructure\\Controller\\ApiRole')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/character-role', 'Universe\\Character\\Infrastructure\\Controller\\ApiRole@destroy');
                Route::get('/character-role-download', 'Universe\\Character\\Infrastructure\\Controller\\ApiRole@download');
                Route::get('/character-role-fields', 'Universe\\Character\\Infrastructure\\Controller\\ApiRole@fields');
                Route::post('/character-role-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiRole@upload');
                Route::get('/character-role-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiRole@uploadStatus');
                Route::delete('/character-role-upload/{id}', 'Universe\\Character\\Infrastructure\\Controller\\ApiRole@deleteUpload');

                Route::apiResource('character-stat', 'Universe\\Character\\Infrastructure\\Controller\\ApiStat')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/character-stat', 'Universe\\Character\\Infrastructure\\Controller\\ApiStat@destroy');
                Route::get('/character-stat-download', 'Universe\\Character\\Infrastructure\\Controller\\ApiStat@download');
                Route::get('/character-stat-fields', 'Universe\\Character\\Infrastructure\\Controller\\ApiStat@fields');
                Route::post('/character-stat-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiStat@upload');
                Route::get('/character-stat-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiStat@uploadStatus');
                Route::delete('/character-stat-upload/{id}', 'Universe\\Character\\Infrastructure\\Controller\\ApiStat@deleteUpload');

                Route::apiResource('character-subcategory-definition', 'Universe\\Character\\Infrastructure\\Controller\\ApiSubcategoryDefinition')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/character-subcategory-definition', 'Universe\\Character\\Infrastructure\\Controller\\ApiSubcategoryDefinition@destroy');
                Route::get('/character-subcategory-definition-download', 'Universe\\Character\\Infrastructure\\Controller\\ApiSubcategoryDefinition@download');
                Route::get('/character-subcategory-definition-fields', 'Universe\\Character\\Infrastructure\\Controller\\ApiSubcategoryDefinition@fields');
                Route::post('/character-subcategory-definition-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiSubcategoryDefinition@upload');
                Route::get('/character-subcategory-definition-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiSubcategoryDefinition@uploadStatus');
                Route::delete('/character-subcategory-definition-upload/{id}', 'Universe\\Character\\Infrastructure\\Controller\\ApiSubcategoryDefinition@deleteUpload');

                Route::apiResource('character-subcategory-type', 'Universe\\Character\\Infrastructure\\Controller\\ApiSubcategoryType')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/character-subcategory-type', 'Universe\\Character\\Infrastructure\\Controller\\ApiSubcategoryType@destroy');
                Route::get('/character-subcategory-type-download', 'Universe\\Character\\Infrastructure\\Controller\\ApiSubcategoryType@download');
                Route::get('/character-subcategory-type-fields', 'Universe\\Character\\Infrastructure\\Controller\\ApiSubcategoryType@fields');
                Route::post('/character-subcategory-type-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiSubcategoryType@upload');
                Route::get('/character-subcategory-type-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiSubcategoryType@uploadStatus');
                Route::delete('/character-subcategory-type-upload/{id}', 'Universe\\Character\\Infrastructure\\Controller\\ApiSubcategoryType@deleteUpload');

                Route::apiResource('character-undead-suffix', 'Universe\\Character\\Infrastructure\\Controller\\ApiUndeadSuffix')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/character-undead-suffix', 'Universe\\Character\\Infrastructure\\Controller\\ApiUndeadSuffix@destroy');
                Route::get('/character-undead-suffix-download', 'Universe\\Character\\Infrastructure\\Controller\\ApiUndeadSuffix@download');
                Route::get('/character-undead-suffix-fields', 'Universe\\Character\\Infrastructure\\Controller\\ApiUndeadSuffix@fields');
                Route::post('/character-undead-suffix-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiUndeadSuffix@upload');
                Route::get('/character-undead-suffix-upload', 'Universe\\Character\\Infrastructure\\Controller\\ApiUndeadSuffix@uploadStatus');
                Route::delete('/character-undead-suffix-upload/{id}', 'Universe\\Character\\Infrastructure\\Controller\\ApiUndeadSuffix@deleteUpload');

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

                Route::apiResource('character', 'Universe\\Character\\Infrastructure\\Controller\\Api')->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::delete('/character', 'Universe\\Character\\Infrastructure\\Controller\\Api@destroy');
                Route::get('/character-download', 'Universe\\Character\\Infrastructure\\Controller\\Api@download');
                Route::get('/character-fields', 'Universe\\Character\\Infrastructure\\Controller\\Api@fields');

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
