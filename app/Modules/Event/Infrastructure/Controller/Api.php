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

class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Event';
    }

    /**
     * Display a listing of own resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $now = new Carbon();
        return response()->json(($this->getTransformerClass())::collection(($this->getModelClass())::where('destinator_id', '=', auth()->user()->id)->where('start_at', '<=', $now)->where('end_at', '>=', $now)->orderBy('created_at', 'desc')->get()));
    }

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
     * Display a listing of summary resource.
     *
     * @return JsonResponse
     */
    public function eventSummary()
    {
        return response()->json([]);
    }
}
