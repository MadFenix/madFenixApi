<?php
namespace App\Modules\Theme\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Theme\Domain\ThemeConfig;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Theme Configuration management
 *
 * APIs for managing theme configurations
 */
class ApiConfig extends ResourceController
{
    /**
     * Display a listing of theme configurations.
     *
     * Get a paginated list of all theme configurations.
     *
     * @param Request $request
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter configurations by name. Example: "Main Config"
     * @bodyParam sorting string Sort configurations by column and direction (column:direction). Example: "created_at:desc"
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Store a newly created theme configuration.
     *
     * Create a new theme configuration with the provided data.
     *
     * @bodyParam name string The name of the configuration. Example: "Main Configuration"
     * @bodyParam name_ecosystem string The ecosystem name. Example: "Phoenix Ecosystem"
     * @bodyParam name_fiscal string The fiscal name. Example: "Phoenix Inc."
     * @bodyParam direction string The direction/address. Example: "123 Main St"
     * @bodyParam city string The city. Example: "Phoenix City"
     * @bodyParam postal_code string The postal code. Example: "12345"
     * @bodyParam state string The state. Example: "Arizona"
     * @bodyParam email string The email address. Example: "contact@phoenix.com"
     * @bodyParam legal_advise text The legal advise text. Example: "Legal advise content..."
     * @bodyParam terms_conditions text The terms and conditions text. Example: "Terms and conditions content..."
     * @bodyParam cookies_policy text The cookies policy text. Example: "Cookies policy content..."
     * @bodyParam privacy_policy text The privacy policy text. Example: "Privacy policy content..."
     * @bodyParam active_coin_free integer Whether free coins are active (0 or 1). Example: 1
     * @bodyParam active_coin_premium integer Whether premium coins are active (0 or 1). Example: 1
     * @bodyParam active_polls integer Whether polls are active (0 or 1). Example: 1
     * @bodyParam active_seasons integer Whether seasons are active (0 or 1). Example: 1
     * @bodyParam active_items integer Whether items are active (0 or 1). Example: 1
     * @bodyParam active_coupons integer Whether coupons are active (0 or 1). Example: 1
     * @bodyParam active_store integer Whether store is active (0 or 1). Example: 1
     * @bodyParam active_referrals integer Whether referrals are active (0 or 1). Example: 1
     * @bodyParam active_hedera integer Whether hedera is active (0 or 1). Example: 0
     * @bodyParam active integer Whether the configuration is active (0 or 1). Example: 0
     * @return JsonResponse
     */
    public function store()
    {
        return parent::store();
    }

    /**
     * Display the specified theme configuration.
     *
     * Get details of a specific theme configuration by ID.
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
     * Update the specified theme configuration.
     *
     * Update an existing theme configuration with the provided data.
     *
     * @param string $account
     * @param int $id
     * @bodyParam name string The name of the configuration. Example: "Main Configuration"
     * @bodyParam name_ecosystem string The ecosystem name. Example: "Phoenix Ecosystem"
     * @bodyParam name_fiscal string The fiscal name. Example: "Phoenix Inc."
     * @bodyParam direction string The direction/address. Example: "123 Main St"
     * @bodyParam city string The city. Example: "Phoenix City"
     * @bodyParam postal_code string The postal code. Example: "12345"
     * @bodyParam state string The state. Example: "Arizona"
     * @bodyParam email string The email address. Example: "contact@phoenix.com"
     * @bodyParam legal_advise text The legal advise text. Example: "Legal advise content..."
     * @bodyParam terms_conditions text The terms and conditions text. Example: "Terms and conditions content..."
     * @bodyParam cookies_policy text The cookies policy text. Example: "Cookies policy content..."
     * @bodyParam privacy_policy text The privacy policy text. Example: "Privacy policy content..."
     * @bodyParam active_coin_free integer Whether free coins are active (0 or 1). Example: 1
     * @bodyParam active_coin_premium integer Whether premium coins are active (0 or 1). Example: 1
     * @bodyParam active_polls integer Whether polls are active (0 or 1). Example: 1
     * @bodyParam active_seasons integer Whether seasons are active (0 or 1). Example: 1
     * @bodyParam active_items integer Whether items are active (0 or 1). Example: 1
     * @bodyParam active_coupons integer Whether coupons are active (0 or 1). Example: 1
     * @bodyParam active_store integer Whether store is active (0 or 1). Example: 1
     * @bodyParam active_referrals integer Whether referrals are active (0 or 1). Example: 1
     * @bodyParam active_hedera integer Whether hedera is active (0 or 1). Example: 0
     * @bodyParam active integer Whether the configuration is active (0 or 1). Example: 0
     * @return JsonResponse
     */
    public function update($account, $id)
    {
        return parent::update($account, $id);
    }

    /**
     * Remove the specified theme configuration.
     *
     * Delete a theme configuration by ID.
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
     * Download theme configurations as CSV or JSON.
     *
     * Export the theme configuration data in CSV or JSON format.
     *
     * @param Request $request
     * @bodyParam type string The file format to download (csv or json). Example: "csv"
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter configurations by name. Example: "Main Config"
     * @bodyParam sorting string Sort configurations by column and direction (column:direction). Example: "created_at:desc"
     * @return Response
     */
    public function download(Request $request)
    {
        return parent::download($request);
    }

    /**
     * List the fields of the theme configuration model.
     *
     * Get the structure and field types of the theme configuration model.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function fields($account)
    {
        return parent::fields($account);
    }

    /**
     * Upload a CSV file for bulk theme configuration processing.
     *
     * Upload a CSV file to create multiple theme configurations at once.
     *
     * @param string $account
     * @bodyParam file file required The CSV file to upload (max 1MB). Must be a CSV file.
     * @bodyParam header_mapping array required Array of headers mapping to theme configuration fields.
     * @return JsonResponse
     */
    public function upload($account)
    {
        return parent::upload($account);
    }

    /**
     * Get the status of a bulk theme configuration upload.
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
     * Delete a bulk theme configuration upload.
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
     * Activate a theme configuration.
     *
     * Set a theme configuration as active and deactivate all others.
     *
     * @param string $account
     * @param int $id
     * @return JsonResponse
     */
    public function activate($account, $id)
    {
        try {
            $config = ThemeConfig::findOrFail($id);
            $config->activate();

            return response()->json([
                'message' => 'Theme configuration activated successfully',
                'data' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error activating theme configuration',
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
        return 'theme-config';
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
        return ThemeConfig::class;
    }

    /**
     * Get the transformer class.
     *
     * @return string
     */
    public function getTransformerClass(): string
    {
        return \App\Modules\Theme\Transformers\ThemeConfig::class;
    }
}
