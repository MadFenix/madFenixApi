<?php


namespace App\Modules\Game\Node\Infrastructure\Controller;

use App\Modules\Base\Domain\BaseDomain;
use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Game\Node\Transformers\NodeSummary;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\Node';
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $user = auth()->user();
        $nodes = ($this->getModelClass())::where('owner_id', $user->id)->orderBy('created_at', 'desc')->get();

        return response()->json(($this->getTransformerClass())::collection($nodes));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return JsonResponse
     * @throws
     */
    public function store()
    {
        $modelClass = $this->getModelClass();
        $transformerClass = $this->getTransformerClass();
        /** @var BaseDomain $model */
        $model = new $modelClass();
        $validator = Validator::make(request()->all(), $model->getValidationContext());

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $model = new $modelClass(request()->all());
        $model->save();

        return response()->json(new $transformerClass($model));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $user = auth()->user();
        $transformerClass = $this->getTransformerClass();
        $node = ($this->getModelClass())::findOrFail($id);
        if ($node->owner_id !== $user->id) {
            throw new \Exception('Can not show this node.');
        }

        return response()->json(new $transformerClass($node));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return JsonResponse
     * @throws
     */
    public function update($id)
    {
        $user = auth()->user();
        $transformerClass = $this->getTransformerClass();
        /** @var BaseDomain $model */
        $model = ($this->getModelClass())::findOrFail($id);
        if ($model->owner_id !== $user->id) {
            throw new \Exception('Can not update this node.');
        }
        $validator = Validator::make(request()->all(), $model->getValidationContext());

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $model->update(request()->all());

        return response()->json(new $transformerClass($model));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $user = auth()->user();
        /** @var BaseDomain $model */
        $model = ($this->getModelClass())::findOrFail($id);
        if ($model->owner_id !== $user->id) {
            throw new \Exception('Can not destroy this node.');
        }

        return response()->json($model->remove());
    }

    /**
     * Display a listing of summary resource.
     *
     * @return JsonResponse
     */
    public function nodeSummary()
    {
        return response()->json(NodeSummary::collection(($this->getModelClass())::all()));
    }
}
