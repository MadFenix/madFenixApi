<?php

namespace App\Modules\Base\Infrastructure\Controller;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ServerErrorException;
use App\Exceptions\ValidationErrorException;
use App\Http\Controllers\Controller;
use App\Modules\Base\Domain\BaseDomain;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

abstract class ResourceController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function formatExceptionError(Throwable $e)
    {

        $message = $e->getMessage();

        if ($e instanceof NotFoundException) {
            $status = 404;
        } else if ($e instanceof ForbiddenException) {
            $status = 403;
        } else if ($e instanceof ServerErrorException) {
            $status = 500;
        } else if ($e instanceof ValidationErrorException) {
            $status = 422;
        } else {
            $status = 500;
        }

        return response()->json([
            "message" => $message,
            "status" => $status
        ], $status);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(($this->getTransformerClass())::collection(($this->getModelClass())::orderBy('created_at', 'desc')->get()));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return JsonResponse
     * @throws
     */
    public function store()
    {
        try {
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
        } catch (\Throwable $th) {
            return $this->formatExceptionError($th);
        }

        return response()->json(new $transformerClass($model));
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $account
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($account, $id)
    {
        try {
            $transformerClass = $this->getTransformerClass();
            $model = ($this->getModelClass())::findOrFail($id);
        } catch (\Throwable $th) {
            return $this->formatExceptionError($th);
        }

        return response()->json(new $transformerClass($model));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  string  $account
     * @param  int  $id
     * @return JsonResponse
     * @throws
     */
    public function update($account, $id)
    {
        try {
            $transformerClass = $this->getTransformerClass();
            /** @var BaseDomain $model */
            $model = ($this->getModelClass())::findOrFail($id);
            $validator = Validator::make(request()->all(), $model->getValidationContext());

            if ($validator->fails()) {
                throw ValidationException::withMessages($validator->errors()->toArray());
            }

            $model->update(request()->all());
        } catch (\Throwable $th) {
            return $this->formatExceptionError($th);
        }

        return response()->json(new $transformerClass($model));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $account
     * @return JsonResponse
     */
    public function destroy($account, Request $request)
    {
        $id = $request->route('id') ?? $request->input('id') ?? $request->query('id');

        try {
            if (!is_array($id)) {
                $id = [$id];
            }
            $response = [];
            foreach ($id as $idValue) {
                /** @var BaseDomain $model */
                $model = ($this->getModelClass())::findOrFail($idValue);

                $response[] = $model->remove();
            }
        } catch (\Throwable $th) {
            return $this->formatExceptionError($th);
        }

        return response()->json($response);
    }

    abstract protected function getModelName(): string;

    protected function getModelClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\' . $modelName . '\\Domain\\' . $lastModelName;
    }

    protected function getTransformerClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\' . $modelName . '\\Transformers\\' . $lastModelName;
    }
}
