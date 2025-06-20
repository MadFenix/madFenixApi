<?php


namespace App\Modules\Blockchain\Block\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @group Subitems management
 *
 * APIs for managing SubItem (specific instances of Items)
 */
class ApiNftIdentification extends ResourceController
{
    /**
     * Display a listing of SubItem.
     *
     * Get a paginated list of all SubItem.
     *
     * @param Request $request
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter items by name. Example: "Phoenix"
     * @bodyParam sorting string Sort items by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter items by parent Item ID. Example: 1
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Store a newly created SubItem.
     *
     * Create a new SubItem with the provided data.
     *
     * @bodyParam name string required The name of the SubItem. Example: "Phoenix #123"
     * @bodyParam description string The description of the SubItem. Example: "A unique phoenix with special attributes"
     * @bodyParam image string The image URL of the SubItem. Example: "https://example.com/nft123.jpg"
     * @bodyParam nft_identification integer required The unique identification number. Example: 123
     * @bodyParam nft_id integer required The ID of the parent Item. Example: 1
     * @bodyParam rarity string The rarity level of the Item. Example: "Legendary"
     * @bodyParam tag_1 string The first tag for the Item. Example: "Fire"
     * @bodyParam tag_2 string The second tag for the Item. Example: "Mythical"
     * @bodyParam tag_3 string The third tag for the Item. Example: "Limited"
     * @bodyParam madfenix_ownership boolean Whether the Item is owned by MadFenix. Example: true
     * @bodyParam user_id integer The ID of the user who owns this Item. Example: 42
     * @bodyParam user_id_hedera integer The ID of the user who owns this Item on Hedera. Example: 42
     *
     * @return JsonResponse
     */
    public function store()
    {
        return parent::store();
    }

    /**
     * Display the specified SubItem.
     *
     * Get details of a specific SubItem by ID.
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
     * Update the specified SubItem.
     *
     * Update an existing SubItem with the provided data.
     *
     * @param string $account
     * @param int $id
     * @bodyParam name string required The name of the SubItem. Example: "Phoenix #123"
     * @bodyParam description string The description of the SubItem. Example: "A unique phoenix with special attributes"
     * @bodyParam image string The image URL of the SubItem. Example: "https://example.com/nft123.jpg"
     * @bodyParam nft_identification integer required The unique identification number. Example: 123
     * @bodyParam nft_id integer required The ID of the parent Item. Example: 1
     * @bodyParam rarity string The rarity level of the Item. Example: "Legendary"
     * @bodyParam tag_1 string The first tag for the Item. Example: "Fire"
     * @bodyParam tag_2 string The second tag for the Item. Example: "Mythical"
     * @bodyParam tag_3 string The third tag for the Item. Example: "Limited"
     * @bodyParam madfenix_ownership boolean Whether the Item is owned by MadFenix. Example: true
     * @bodyParam user_id integer The ID of the user who owns this Item. Example: 42
     * @bodyParam user_id_hedera integer The ID of the user who owns this Item on Hedera. Example: 42
     *
     * @return JsonResponse
     */
    public function update($account, $id)
    {
        return parent::update($account, $id);
    }

    /**
     * Remove the specified SubItem.
     *
     * Delete an SubItem by ID.
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
     * Download SubItem as CSV or JSON.
     *
     * Export the SubItem data in CSV or JSON format.
     *
     * @param Request $request
     * @bodyParam type string The file format to download (csv or json). Example: "csv"
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter items by name. Example: "Phoenix"
     * @bodyParam sorting string Sort items by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter items by parent Item ID. Example: 1
     * @return JsonResponse|StreamedResponse
     */
    public function download(Request $request)
    {
        return parent::download($request);
    }

    /**
     * List the fields of the SubItem model.
     *
     * Get the structure and field types of the SubItem model.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function fields($account)
    {
        return parent::fields($account);
    }

    /**
     * Upload a CSV file for bulk SubItem processing.
     *
     * Upload a CSV file to create multiple SubItem at once.
     *
     * @param string $account
     * @bodyParam file file required The CSV file to upload (max 1MB). Must be a CSV file.
     * @bodyParam header_mapping array required Array of headers mapping to SubItem fields.
     * @return JsonResponse
     */
    public function upload($account)
    {
        return parent::upload($account);
    }

    /**
     * Get the status of a bulk SubItem upload.
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
     * Delete a bulk SubItem upload.
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
        return 'Blockchain\\NftIdentification';
    }

    protected function getParentIdentificator(): string
    {
        return 'nft_id';
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
