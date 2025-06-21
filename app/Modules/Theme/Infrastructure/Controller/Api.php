<?php
namespace App\Modules\Theme\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Theme\Domain\Theme;
use App\Modules\Theme\Domain\ThemeConfig;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Theme management
 *
 * APIs for managing themes
 */
class Api extends ResourceController
{
    /**
     * Display a listing of themes.
     *
     * Get a paginated list of all themes.
     *
     * @param Request $request
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter themes by name. Example: "Dark Theme"
     * @bodyParam sorting string Sort themes by column and direction (column:direction). Example: "created_at:desc"
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Store a newly created theme.
     *
     * Create a new theme with the provided data.
     *
     * @bodyParam name string The name of the theme. Example: "Dark Theme"
     * @bodyParam logo string The logo URL of the theme. Example: "https://example.com/logo.png"
     * @bodyParam color_primary string The primary color of the theme. Example: "#FF5733"
     * @bodyParam color_secondary string The secondary color of the theme. Example: "#33FF57"
     * @bodyParam color_white0 string The white color of the theme. Example: "#FFFFFF"
     * @bodyParam color_black string The black color of the theme. Example: "#000000"
     * @bodyParam color_background_secondary string The secondary background color of the theme. Example: "#F5F5F5"
     * @bodyParam title_profile string The profile title text. Example: "User Profile"
     * @bodyParam title_avatar string The avatar title text. Example: "Avatar"
     * @bodyParam title_state string The state title text. Example: "Status"
     * @bodyParam title_coin_premium string The premium coin title text. Example: "Gold"
     * @bodyParam title_coin_free string The free coin title text. Example: "Feathers"
     * @bodyParam title_polls string The polls title text. Example: "Surveys"
     * @bodyParam title_items string The items title text. Example: "Items"
     * @bodyParam title_collections string The collections title text. Example: "Collections"
     * @bodyParam title_season string The season title text. Example: "Season"
     * @bodyParam title_season_level string The season level title text. Example: "Level"
     * @bodyParam title_season_points string The season points title text. Example: "Points"
     * @bodyParam title_season_premium string The season premium title text. Example: "Premium"
     * @bodyParam title_store string The store title text. Example: "Store"
     * @bodyParam title_store_h1 string The store h1 title text. Example: "Welcome to the Store"
     * @bodyParam active integer Whether the theme is active (0 or 1). Example: 0
     * @return JsonResponse
     */
    public function store()
    {
        return parent::store();
    }

    /**
     * Display the specified theme.
     *
     * Get details of a specific theme by ID.
     *
     * @param string $account
     * @param int $id
     * @return JsonResponse
     */
    public function show($account, $id)
    {
        return parent::show($account, $id);
    }

    /**
     * Update the specified theme.
     *
     * Update an existing theme with the provided data.
     *
     * @param string $account
     * @param int $id
     * @bodyParam name string The name of the theme. Example: "Dark Theme"
     * @bodyParam logo string The logo URL of the theme. Example: "https://example.com/logo.png"
     * @bodyParam color_primary string The primary color of the theme. Example: "#FF5733"
     * @bodyParam color_secondary string The secondary color of the theme. Example: "#33FF57"
     * @bodyParam color_white0 string The white color of the theme. Example: "#FFFFFF"
     * @bodyParam color_black string The black color of the theme. Example: "#000000"
     * @bodyParam color_background_secondary string The secondary background color of the theme. Example: "#F5F5F5"
     * @bodyParam title_profile string The profile title text. Example: "User Profile"
     * @bodyParam title_avatar string The avatar title text. Example: "Avatar"
     * @bodyParam title_state string The state title text. Example: "Status"
     * @bodyParam title_coin_premium string The premium coin title text. Example: "Gold"
     * @bodyParam title_coin_free string The free coin title text. Example: "Feathers"
     * @bodyParam title_polls string The polls title text. Example: "Surveys"
     * @bodyParam title_items string The items title text. Example: "Items"
     * @bodyParam title_collections string The collections title text. Example: "Collections"
     * @bodyParam title_season string The season title text. Example: "Season"
     * @bodyParam title_season_level string The season level title text. Example: "Level"
     * @bodyParam title_season_points string The season points title text. Example: "Points"
     * @bodyParam title_season_premium string The season premium title text. Example: "Premium"
     * @bodyParam title_store string The store title text. Example: "Store"
     * @bodyParam title_store_h1 string The store h1 title text. Example: "Welcome to the Store"
     * @bodyParam active integer Whether the theme is active (0 or 1). Example: 0
     * @return JsonResponse
     */
    public function update($account, $id)
    {
        return parent::update($account, $id);
    }

    /**
     * Remove the specified theme.
     *
     * Delete a theme by ID.
     *
     * @param string $account
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy($account, Request $request)
    {
        return parent::destroy($account, $request);
    }

    /**
     * Download themes as CSV or JSON.
     *
     * Export the theme data in CSV or JSON format.
     *
     * @param Request $request
     * @bodyParam type string The file format to download (csv or json). Example: "csv"
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter themes by name. Example: "Dark Theme"
     * @bodyParam sorting string Sort themes by column and direction (column:direction). Example: "created_at:desc"
     * @return Response
     */
    public function download(Request $request)
    {
        return parent::download($request);
    }

    /**
     * List the fields of the theme model.
     *
     * Get the structure and field types of the theme model.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function fields($account)
    {
        return parent::fields($account);
    }

    /**
     * Upload a CSV file for bulk theme processing.
     *
     * Upload a CSV file to create multiple themes at once.
     *
     * @param string $account
     * @bodyParam file file required The CSV file to upload (max 1MB). Must be a CSV file.
     * @bodyParam header_mapping array required Array of headers mapping to theme fields.
     * @return JsonResponse
     */
    public function upload($account)
    {
        return parent::upload($account);
    }

    /**
     * Get the status of a bulk theme upload.
     *
     * Check the progress of a previously submitted bulk upload.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function uploadStatus($account)
    {
        return parent::uploadStatus($account);
    }

    /**
     * Delete a bulk theme upload.
     *
     * Remove a pending or processing bulk upload.
     *
     * @param string $account
     * @param int $id
     * @return JsonResponse
     */
    public function deleteUpload($account, $id)
    {
        return parent::deleteUpload($account, $id);
    }

    /**
     * Activate a theme.
     *
     * Set a theme as active and deactivate all others.
     *
     * @param string $account
     * @param int $id
     * @return JsonResponse
     */
    public function activate($account, $id)
    {
        try {
            $theme = Theme::findOrFail($id);
            $theme->activate();

            return response()->json([
                'message' => 'Theme activated successfully',
                'data' => $theme
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error activating theme',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the model name.
     *
     * @return string
     */
    public function getModelName(): string
    {
        return 'theme';
    }

    /**
     * Get the parent identificator.
     *
     * @return string
     */
    public function getParentIdentificator(): string
    {
        return '';
    }

    /**
     * Get the model class.
     *
     * @return string
     */
    public function getModelClass(): string
    {
        return Theme::class;
    }

    /**
     * Get the transformer class.
     *
     * @return string
     */
    public function getTransformerClass(): string
    {
        return \App\Modules\Theme\Transformers\Theme::class;
    }

    /**
     * Get the active theme.
     *
     * Retrieve the currently active theme and all its properties.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function getActiveTheme($account)
    {
        try {
            $theme = Theme::where('active', 1)->first();

            if (!$theme) {
                return response()->json([
                    'message' => 'No active theme found',
                    'data' => null
                ], 404);
            }

            $transformer = $this->getTransformerClass();

            $config = ThemeConfig::where('active', 1)->first();

            if (!$config) {
                return response()->json([
                    'message' => 'No active theme configuration found',
                    'data' => null
                ], 404);
            }

            $transformerThemeConfig = \App\Modules\Theme\Transformers\ThemeConfig::class;

            $data = new \stdClass();
            $data->theme = new $transformer($theme);
            $data->config = new $transformerThemeConfig($config);

            return response()->json([
                'message' => 'Active theme retrieved successfully',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving active theme',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
