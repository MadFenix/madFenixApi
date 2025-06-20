<?php


namespace App\Modules\User\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group User management admin
 *
 * APIs for managing users by admins
 */
class ApiManager extends ResourceController
{
    /**
     * Display a listing of users.
     *
     * Get a paginated list of all users.
     *
     * @param Request $request
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter users by name or email. Example: "john"
     * @bodyParam sorting string Sort users by column and direction (column:direction). Example: "created_at:desc"
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Store a newly created user.
     *
     * Create a new user with the provided data.
     *
     * @bodyParam name string required The name of the user. Example: "John Doe"
     * @bodyParam email string required The email address of the user. Example: "john@example.com"
     * @bodyParam password string required The password for the user (min 8 characters). Example: "password123"
     * @bodyParam newsletter boolean Whether the user wants to receive the newsletter. Example: true
     * @bodyParam eth_wallet string The Ethereum wallet address of the user. Example: "0x1234567890abcdef"
     * @bodyParam ip string The IP address of the user. Example: "192.168.1.1"
     * @bodyParam two_factor_pre_enabled boolean Whether two-factor authentication is pre-enabled. Example: false
     * @bodyParam two_factor_enabled boolean Whether two-factor authentication is enabled. Example: false
     * @bodyParam two_factor_secret string The two-factor authentication secret. Example: "ABCDEFGHIJKLMNOP"
     * @return JsonResponse
     */
    public function store()
    {
        return parent::store();
    }

    /**
     * Display the specified user.
     *
     * Get details of a specific user by ID.
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
     * Update the specified user.
     *
     * Update an existing user with the provided data.
     *
     * @param string $account
     * @param int $id
     * @bodyParam name string required The name of the user. Example: "John Doe"
     * @bodyParam email string required The email address of the user. Example: "john@example.com"
     * @bodyParam newsletter boolean Whether the user wants to receive the newsletter. Example: true
     * @bodyParam eth_wallet string The Ethereum wallet address of the user. Example: "0x1234567890abcdef"
     * @bodyParam ip string The IP address of the user. Example: "192.168.1.1"
     * @bodyParam two_factor_pre_enabled boolean Whether two-factor authentication is pre-enabled. Example: false
     * @bodyParam two_factor_enabled boolean Whether two-factor authentication is enabled. Example: false
     * @bodyParam two_factor_secret string The two-factor authentication secret. Example: "ABCDEFGHIJKLMNOP"
     * @return JsonResponse
     */
    public function update($account, $id)
    {
        return parent::update($account, $id);
    }

    /**
     * Remove the specified user.
     *
     * Delete a user by ID.
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
     * Download users as CSV or JSON.
     *
     * Export the user data in CSV or JSON format.
     *
     * @param Request $request
     * @bodyParam type string The file format to download (csv or json). Example: "csv"
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter users by name or email. Example: "john"
     * @bodyParam sorting string Sort users by column and direction (column:direction). Example: "created_at:desc"
     * @return JsonResponse|StreamedResponse
     */
    public function download(Request $request)
    {
        return parent::download($request);
    }

    /**
     * List the fields of the User model.
     *
     * Get the structure and field types of the User model.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function fields($account)
    {
        return parent::fields($account);
    }

    /**
     * Upload a CSV file for bulk User processing.
     *
     * Upload a CSV file to create multiple Users at once.
     *
     * @param string $account
     * @bodyParam file file required The CSV file to upload (max 1MB). Must be a CSV file.
     * @bodyParam header_mapping array required Array of headers mapping to User fields.
     * @return JsonResponse
     */
    public function upload($account)
    {
        return parent::upload($account);
    }

    /**
     * Get the status of a bulk User upload.
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
     * Delete a bulk User upload.
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

    protected function getModelName(): string
    {
        return 'User\\User';
    }

    protected function getModelClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\User\\Domain\\' . $lastModelName;
    }

    protected function getTransformerClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\User\\Transformers\\UserSummary';
    }
}
