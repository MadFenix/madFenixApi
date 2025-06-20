<?php


namespace App\Modules\Blockchain\Block\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @group Item management
 *
 * APIs for managing Items
 */
class ApiNft extends ResourceController
{
    /**
     * Display a listing of Items.
     *
     * Get a paginated list of all Items.
     *
     * @param Request $request
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter items by name. Example: "Phoenix"
     * @bodyParam sorting string Sort items by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter items by parent ID. Example: 1
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Store a newly created Item.
     *
     * Create a new Item with the provided data.
     *
     * @bodyParam name string required The name of the Item. Example: "Rare Phoenix"
     * @bodyParam short_description string The short description of the Item. Example: "A rare phoenix collectible"
     * @bodyParam description string The detailed description of the Item. Example: "This is a detailed description of the rare phoenix collectible"
     * @bodyParam category string The category of the Item. Example: "Collectible"
     * @bodyParam subcategory string The subcategory of the Item. Example: "Mythical"
     * @bodyParam portrait_image string The portrait image URL of the Item. Example: "https://example.com/portrait.jpg"
     * @bodyParam featured_image string The featured image URL of the Item. Example: "https://example.com/featured.jpg"
     * @bodyParam token_props integer required The token properties. Example: 1
     * @bodyParam token_realm integer required The token realm. Example: 2
     * @bodyParam token_number integer required The token number. Example: 3
     *
     * @return JsonResponse
     */
    public function store()
    {
        return parent::store();
    }

    /**
     * Display the specified Item.
     *
     * Get details of a specific Item by ID.
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
     * Update the specified Item.
     *
     * Update an existing Item with the provided data.
     *
     * @param string $account
     * @param int $id
     * @bodyParam name string required The name of the Item. Example: "Rare Phoenix"
     * @bodyParam short_description string The short description of the Item. Example: "A rare phoenix collectible"
     * @bodyParam description string The detailed description of the Item. Example: "This is a detailed description of the rare phoenix collectible"
     * @bodyParam category string The category of the Item. Example: "Collectible"
     * @bodyParam subcategory string The subcategory of the Item. Example: "Mythical"
     * @bodyParam portrait_image string The portrait image URL of the Item. Example: "https://example.com/portrait.jpg"
     * @bodyParam featured_image string The featured image URL of the Item. Example: "https://example.com/featured.jpg"
     * @bodyParam token_props integer required The token properties. Example: 1
     * @bodyParam token_realm integer required The token realm. Example: 2
     * @bodyParam token_number integer required The token number. Example: 3
     *
     * @return JsonResponse
     */
    public function update($account, $id)
    {
        return parent::update($account, $id);
    }

    /**
     * Remove the specified Item.
     *
     * Delete an Item by ID.
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
     * Download Items as CSV or JSON.
     *
     * Export the Item data in CSV or JSON format.
     *
     * @param Request $request
     * @bodyParam type string The file format to download (csv or json). Example: "csv"
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter items by name. Example: "Phoenix"
     * @bodyParam sorting string Sort items by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter items by parent ID. Example: 1
     * @return JsonResponse|StreamedResponse
     */
    public function download(Request $request)
    {
        return parent::download($request);
    }

    /**
     * List the fields of the Item model.
     *
     * Get the structure and field types of the Item model.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function fields($account)
    {
        return parent::fields($account);
    }

    /**
     * Upload a CSV file for bulk Item processing.
     *
     * Upload a CSV file to create multiple Items at once.
     *
     * @param string $account
     * @bodyParam file file required The CSV file to upload (max 1MB). Must be a CSV file.
     * @bodyParam header_mapping array required Array of headers mapping to Item fields.
     * @return JsonResponse
     */
    public function upload($account)
    {
        return parent::upload($account);
    }

    /**
     * Get the status of a bulk Item upload.
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
     * Delete a bulk Item upload.
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
        return 'Blockchain\\Nft';
    }

    protected function getModelClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\Blockchain\\Block\\Domain\\' . $lastModelName;
    }

    protected function getTransformerClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\Blockchain\\Block\\Transformers\\' . $lastModelName;
    }
}
