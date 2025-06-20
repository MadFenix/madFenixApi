<?php


namespace App\Modules\Event\Infrastructure\Controller;

use App\Modules\Base\Domain\BaseDomain;
use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Blockchain\Block\Domain\BlockchainHistorical;
use App\Modules\Event\Domain\Event;
use App\Modules\Store\Domain\Product;
use App\Modules\Store\Domain\ProductOrder;
use App\Modules\User\Domain\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @group Events meta management
 *
 * APIs for managing events meta
 */
class ApiMeta extends ResourceController
{
    protected function getNameParameter(): string
    {
        return 'description';
    }

    protected function getModelName(): string
    {
        return 'Event\\EventMeta';
    }

    protected function getModelClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\Event\\Domain\\' . $lastModelName;
    }

    protected function getTransformerClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\Event\\Transformers\\' . $lastModelName;
    }

    /**
     * Display a listing of event metas.
     *
     * Get a paginated list of all event metas.
     *
     * @param Request $request
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter event metas by description. Example: "Important"
     * @bodyParam sorting string Sort event metas by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter event metas by parent ID. Example: 1
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Store a newly created event meta.
     *
     * Create a new event meta with the provided data.
     *
     * @bodyParam creator_id integer required The ID of the user creating the event meta. Example: 1
     * @bodyParam description string required The short description of the event meta (4-255 chars). Example: "New event meta"
     * @bodyParam details string required The detailed description of the event meta (8-2000 chars). Example: "This is a detailed description of the event meta with all relevant information."
     * @bodyParam product_gift_id integer The ID of the product gift. Example: 10
     * @bodyParam start_at datetime required The start date and time of the event meta. Example: "2023-01-01 00:00:00"
     * @bodyParam end_at datetime required The end date and time of the event meta. Example: "2023-01-31 23:59:59"
     * @return JsonResponse
     */
    public function store()
    {
        return parent::store();
    }

    /**
     * Display the specified event meta.
     *
     * Get details of a specific event meta by ID.
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
     * Update the specified event meta.
     *
     * Update an existing event meta with the provided data.
     *
     * @param string $account
     * @param int $id
     * @bodyParam creator_id integer required The ID of the user creating the event meta. Example: 1
     * @bodyParam description string required The short description of the event meta (4-255 chars). Example: "Updated event meta"
     * @bodyParam details string required The detailed description of the event meta (8-2000 chars). Example: "This is an updated detailed description of the event meta with all relevant information."
     * @bodyParam product_gift_id integer The ID of the product gift. Example: 10
     * @bodyParam start_at datetime required The start date and time of the event meta. Example: "2023-01-01 00:00:00"
     * @bodyParam end_at datetime required The end date and time of the event meta. Example: "2023-01-31 23:59:59"
     * @return JsonResponse
     */
    public function update($account, $id)
    {
        return parent::update($account, $id);
    }

    /**
     * Remove the specified event meta.
     *
     * Delete an event meta by ID.
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
     * Download event metas as CSV or JSON.
     *
     * Export the event meta data in CSV or JSON format.
     *
     * @param Request $request
     * @bodyParam type string The file format to download (csv or json). Example: "csv"
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter event metas by description. Example: "Important"
     * @bodyParam sorting string Sort event metas by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter event metas by parent ID. Example: 1
     * @return JsonResponse|StreamedResponse
     */
    public function download(Request $request)
    {
        return parent::download($request);
    }

    /**
     * List the fields of the event meta model.
     *
     * Get the structure and field types of the event meta model.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function fields($account)
    {
        return parent::fields($account);
    }

    /**
     * Upload a CSV file for bulk event meta processing.
     *
     * Upload a CSV file to create multiple event metas at once.
     *
     * @param string $account
     * @bodyParam file file required The CSV file to upload (max 1MB). Must be a CSV file.
     * @bodyParam header_mapping array required Array of headers mapping to event meta fields.
     * @return JsonResponse
     */
    public function upload($account)
    {
        return parent::upload($account);
    }

    /**
     * Get the status of a bulk event meta upload.
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
     * Delete a bulk event meta upload.
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
}
