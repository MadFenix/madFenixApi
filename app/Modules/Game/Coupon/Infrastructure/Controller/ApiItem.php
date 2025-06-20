<?php


namespace App\Modules\Game\Coupon\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

/**
 * @group Item Coupons management
 *
 * APIs for managing item coupons
 */
class ApiItem extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\CouponItem';
    }

    protected function getNameParameter(): string
    {
        return 'coupon';
    }

    protected function getModelClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\Game\\Coupon\\Domain\\' . $lastModelName;
    }

    protected function getTransformerClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\Game\\Coupon\\Transformers\\' . $lastModelName;
    }

    /**
     * Display a listing of item coupons.
     *
     * Get a paginated list of all item coupons.
     *
     * @param \Illuminate\Http\Request $request
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter coupons by code. Example: "ITEM"
     * @bodyParam sorting string Sort coupons by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter coupons by parent ID. Example: 1
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(\Illuminate\Http\Request $request)
    {
        return parent::index($request);
    }

    /**
     * Store a newly created item coupon.
     *
     * Create a new item coupon with the provided data.
     *
     * @bodyParam coupon string required The coupon code (4-150 chars). Example: "ITEM2023"
     * @bodyParam nft_id integer The ID of the NFT to award. Example: 5
     * @bodyParam rarity string The rarity filter for NFTs. Example: "Legendary,Rare"
     * @bodyParam tags string The tags filter for NFTs. Example: "Fire,Water"
     * @bodyParam nft_serial_greater_equal integer The minimum serial number for NFTs. Example: 100
     * @bodyParam nft_serial_less_equal integer The maximum serial number for NFTs. Example: 200
     * @bodyParam uses integer The current number of uses. Example: 0
     * @bodyParam max_uses integer The maximum number of uses allowed. Example: 1000
     * @bodyParam start_date datetime required The start date and time of the coupon validity. Example: "2023-01-01 00:00:00"
     * @bodyParam end_date datetime required The end date and time of the coupon validity. Example: "2023-12-31 23:59:59"
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        return parent::store();
    }

    /**
     * Display the specified item coupon.
     *
     * Get details of a specific item coupon by ID.
     *
     * @param string $account
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($account, $id)
    {
        return parent::show($account, $id);
    }

    /**
     * Update the specified item coupon.
     *
     * Update an existing item coupon with the provided data.
     *
     * @param string $account
     * @param int $id
     * @bodyParam coupon string required The coupon code (4-150 chars). Example: "ITEM2023"
     * @bodyParam nft_id integer The ID of the NFT to award. Example: 5
     * @bodyParam rarity string The rarity filter for NFTs. Example: "Legendary,Rare"
     * @bodyParam tags string The tags filter for NFTs. Example: "Fire,Water"
     * @bodyParam nft_serial_greater_equal integer The minimum serial number for NFTs. Example: 100
     * @bodyParam nft_serial_less_equal integer The maximum serial number for NFTs. Example: 200
     * @bodyParam uses integer The current number of uses. Example: 0
     * @bodyParam max_uses integer The maximum number of uses allowed. Example: 1000
     * @bodyParam start_date datetime required The start date and time of the coupon validity. Example: "2023-01-01 00:00:00"
     * @bodyParam end_date datetime required The end date and time of the coupon validity. Example: "2023-12-31 23:59:59"
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($account, $id)
    {
        return parent::update($account, $id);
    }

    /**
     * Remove the specified item coupon.
     *
     * Delete an item coupon by ID.
     *
     * @param string $account
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($account, \Illuminate\Http\Request $request)
    {
        return parent::destroy($account, $request);
    }

    /**
     * Download item coupons as CSV or JSON.
     *
     * Export the item coupon data in CSV or JSON format.
     *
     * @param \Illuminate\Http\Request $request
     * @bodyParam type string The file format to download (csv or json). Example: "csv"
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter coupons by code. Example: "ITEM"
     * @bodyParam sorting string Sort coupons by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter coupons by parent ID. Example: 1
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(\Illuminate\Http\Request $request)
    {
        return parent::download($request);
    }

    /**
     * List the fields of the item coupon model.
     *
     * Get the structure and field types of the item coupon model.
     *
     * @param string $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function fields($account)
    {
        return parent::fields($account);
    }

    /**
     * Upload a CSV file for bulk item coupon processing.
     *
     * Upload a CSV file to create multiple item coupons at once.
     *
     * @param string $account
     * @bodyParam file file required The CSV file to upload (max 1MB). Must be a CSV file.
     * @bodyParam header_mapping array required Array of headers mapping to item coupon fields.
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload($account)
    {
        return parent::upload($account);
    }

    /**
     * Get the status of a bulk item coupon upload.
     *
     * Check the progress of a previously submitted bulk upload.
     *
     * @param string $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadStatus($account)
    {
        return parent::uploadStatus($account);
    }

    /**
     * Delete a bulk item coupon upload.
     *
     * Remove a pending or processing bulk upload.
     *
     * @param string $account
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUpload($account, $id)
    {
        return parent::deleteUpload($account, $id);
    }
}
