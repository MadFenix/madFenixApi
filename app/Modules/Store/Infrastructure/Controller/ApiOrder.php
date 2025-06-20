<?php


namespace App\Modules\Store\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Blockchain\Block\Domain\BlockchainHistorical;
use App\Modules\Blockchain\Block\Domain\NftIdentification;
use App\Modules\Event\Domain\Event;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Game\Season\Domain\SeasonRewardRedeemed;
use App\Modules\Store\Domain\Product;
use App\Modules\Store\Domain\ProductOrder;
use App\Modules\User\Domain\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;
use Stripe\Webhook;

/**
 * @group Product Order management
 *
 * APIs for managing product orders
 */
class ApiOrder extends ResourceController
{
    /**
     * Display a listing of product orders.
     *
     * Get a paginated list of all product orders.
     *
     * @param Request $request
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter product orders by ID. Example: "123"
     * @bodyParam sorting string Sort product orders by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter product orders by product ID. Example: 1
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Store a newly created product order.
     *
     * Create a new product order with the provided data.
     *
     * @bodyParam product_id integer required The ID of the product being ordered. Example: 1
     * @bodyParam user_id integer required The ID of the user placing the order. Example: 42
     * @bodyParam payment_validated integer Whether the payment has been validated (0 or 1). Example: 0
     * @bodyParam is_gift integer Whether the order is a gift (0 or 1). Example: 0
     * @return JsonResponse
     */
    public function store()
    {
        return parent::store();
    }

    /**
     * Display the specified product order.
     *
     * Get details of a specific product order by ID.
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
     * Update the specified product order.
     *
     * Update an existing product order with the provided data.
     *
     * @param string $account
     * @param int $id
     * @bodyParam product_id integer required The ID of the product being ordered. Example: 1
     * @bodyParam user_id integer required The ID of the user placing the order. Example: 42
     * @bodyParam payment_validated integer Whether the payment has been validated (0 or 1). Example: 1
     * @bodyParam is_gift integer Whether the order is a gift (0 or 1). Example: 0
     * @return JsonResponse
     */
    public function update($account, $id)
    {
        return parent::update($account, $id);
    }

    /**
     * Remove the specified product order.
     *
     * Delete a product order by ID.
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
     * Download product orders as CSV or JSON.
     *
     * Export the product order data in CSV or JSON format.
     *
     * @param Request $request
     * @bodyParam type string The file format to download (csv or json). Example: "csv"
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter product orders by ID. Example: "123"
     * @bodyParam sorting string Sort product orders by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter product orders by product ID. Example: 1
     * @return JsonResponse|StreamedResponse
     */
    public function download(Request $request)
    {
        return parent::download($request);
    }

    /**
     * List the fields of the ProductOrder model.
     *
     * Get the structure and field types of the ProductOrder model.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function fields($account)
    {
        return parent::fields($account);
    }

    /**
     * Upload a CSV file for bulk ProductOrder processing.
     *
     * Upload a CSV file to create multiple ProductOrders at once.
     *
     * @param string $account
     * @bodyParam file file required The CSV file to upload (max 1MB). Must be a CSV file.
     * @bodyParam header_mapping array required Array of headers mapping to ProductOrder fields.
     * @return JsonResponse
     */
    public function upload($account)
    {
        return parent::upload($account);
    }

    /**
     * Get the status of a bulk ProductOrder upload.
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
     * Delete a bulk ProductOrder upload.
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
        return 'Store\\ProductOrder';
    }

    protected function getParentIdentificator(): string
    {
        return 'product_id';
    }

    protected function getModelClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\Store\\Domain\\' . $lastModelName;
    }

    protected function getTransformerClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\Store\\Transformers\\' . $lastModelName;
    }
}
