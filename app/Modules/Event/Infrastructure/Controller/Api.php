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
 * @group Event management
 *
 * APIs for managing events
 */
class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Event';
    }

    /**
     * Display a listing of current events for the authenticated user.
     *
     * Get a list of all active events for the current user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $now = new Carbon();
        return response()->json(($this->getTransformerClass())::collection(($this->getModelClass())::where('destinator_id', '=', auth()->user()->id)->where('start_at', '<=', $now)->where('end_at', '>=', $now)->orderBy('created_at', 'desc')->get()));
    }

    /**
     * Store a newly created event.
     *
     * Create a new event with the provided data.
     *
     * @bodyParam creator_id integer required The ID of the user creating the event. Example: 1
     * @bodyParam destinator_id integer required The ID of the user receiving the event. Example: 2
     * @bodyParam description string required The short description of the event (4-255 chars). Example: "New event notification"
     * @bodyParam details string required The detailed description of the event (8-2000 chars). Example: "This is a detailed description of the event with all relevant information."
     * @bodyParam product_gift_delivered integer The ID of the delivered product gift. Example: 5
     * @bodyParam product_gift_id integer The ID of the product gift. Example: 10
     * @bodyParam start_at datetime required The start date and time of the event. Example: "2023-01-01 00:00:00"
     * @bodyParam end_at datetime required The end date and time of the event. Example: "2023-01-31 23:59:59"
     * @return JsonResponse
     */
    public function store()
    {
        return parent::store();
    }

    /**
     * Display the specified event.
     *
     * Get details of a specific event by ID.
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
     * Update the specified event.
     *
     * Update an existing event with the provided data.
     *
     * @param string $account
     * @param int $id
     * @bodyParam creator_id integer required The ID of the user creating the event. Example: 1
     * @bodyParam destinator_id integer required The ID of the user receiving the event. Example: 2
     * @bodyParam description string required The short description of the event (4-255 chars). Example: "Updated event notification"
     * @bodyParam details string required The detailed description of the event (8-2000 chars). Example: "This is an updated detailed description of the event with all relevant information."
     * @bodyParam product_gift_delivered integer The ID of the delivered product gift. Example: 5
     * @bodyParam product_gift_id integer The ID of the product gift. Example: 10
     * @bodyParam start_at datetime required The start date and time of the event. Example: "2023-01-01 00:00:00"
     * @bodyParam end_at datetime required The end date and time of the event. Example: "2023-01-31 23:59:59"
     * @return JsonResponse
     */
    public function update($account, $id)
    {
        return parent::update($account, $id);
    }

    /**
     * Remove the specified event.
     *
     * Delete an event by ID.
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
     * Download events as CSV or JSON.
     *
     * Export the event data in CSV or JSON format.
     *
     * @param Request $request
     * @bodyParam type string The file format to download (csv or json). Example: "csv"
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter events by description. Example: "Important"
     * @bodyParam sorting string Sort events by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter events by parent ID. Example: 1
     * @return JsonResponse|StreamedResponse
     */
    public function download(Request $request)
    {
        return parent::download($request);
    }

    /**
     * List the fields of the event model.
     *
     * Get the structure and field types of the event model.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function fields($account)
    {
        return parent::fields($account);
    }

    /**
     * Upload a CSV file for bulk event processing.
     *
     * Upload a CSV file to create multiple events at once.
     *
     * @param string $account
     * @bodyParam file file required The CSV file to upload (max 1MB). Must be a CSV file.
     * @bodyParam header_mapping array required Array of headers mapping to event fields.
     * @return JsonResponse
     */
    public function upload($account)
    {
        return parent::upload($account);
    }

    /**
     * Get the status of a bulk event upload.
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
     * Delete a bulk event upload.
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
     * Mark an event as read.
     *
     * Update the read_at timestamp for a specific event.
     *
     * @param Request $request
     * @bodyParam event_id integer required The ID of the event to mark as read. Example: 1
     * @return JsonResponse
     */
    public function readEvent(Request $request) {
        $data = $request->validate(['event_id' => 'required']);

        /** @var User $user */
        $user = auth()->user();

        $event = Event::where('id', '=', $data['event_id'])->where('destinator_id', '=', $user->id)->first();
        if (!$event) {
            return response()->json('Evento del usuario no encontrado.', 404);
        }

        $event->read_at = new Carbon();
        $eventSaved = $event->save();

        return ($eventSaved)
            ? response()->json('Evento guardado.')
            : response()->json('Error al guardar el evento.', 500);
    }

    /**
     * Get event summary.
     *
     * Retrieve a summary of events for the current user.
     *
     * @return JsonResponse
     */
    public function eventSummary()
    {
        return response()->json([]);
    }
}
