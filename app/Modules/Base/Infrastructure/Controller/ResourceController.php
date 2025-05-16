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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

abstract class ResourceController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function getNameParameter(): string
    {
        return 'name';
    }

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

    protected function getData(Request $request, $withoutLimit = false)
    {
        // Define and apply validation rules
        $rules = [
            'page'    => 'nullable|integer|min:0',
            'limit'   => 'nullable|integer|min:1|max:100',
            'filter'  => 'nullable|string|max:255',
            'sorting' => 'nullable|string'
        ];

        $validator = Validator::make($request->all(), $rules);

        // Check for validation errors and return a custom JSON response if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $validated = $validator->valid();

        // Retrieve query parameters and set defaults
        $page = $validated['page'] ?? 0;
        $limit = $validated['limit'] ?? 5;
        $filter = $validated['filter'] ?? '';
        $sorting = $validated['sorting'] ?? 'created_at:desc';
        list($sortColumn, $sortDirection) = explode(':', $sorting);

        // The main model query
        $query = ($this->getModelClass())::orderBy($sortColumn, $sortDirection);

        // Apply filtering
        if(!empty($filter)){
            $query = $query->where($this->getNameParameter(), 'like', '%' . $filter . '%');
        }

        // Get the total count
        $totaData = $query->count();

        // Apply pagination
        if (!$withoutLimit) {
            $query = $query->skip($page * $limit)->take($limit);
        }
        $paginated = $query->get();

        $return = new \stdClass();
        $return->data = ($this->getTransformerClass())::collection($paginated);
        $return->metadata = new \stdClass();
        $return->metadata->page = $page;
        $return->metadata->limit = $limit;
        $return->metadata->totaData = $totaData;
        $return->metadata->filter = $filter;
        $return->metadata->sorting = $sorting;

        return $return;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        /** @var \stdClass|JsonResponse $data */
        $data = $this->getData($request);

        if ($data instanceof JsonResponse) {
            return $data;
        }

        // Return transformed and paginated results
        return response()->json($data);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse|StreamedResponse
     */
    public function download(Request $request)
    {
        $rules = [
            'type' => 'string'
        ];

        $validator = Validator::make($request->all(), $rules);

        // Check for validation errors and return a custom JSON response if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $validated = $validator->valid();

        /** @var \stdClass|JsonResponse $data */
        $data = $this->getData($request, true);

        if ($data instanceof JsonResponse) {
            return $data;
        }
        /** @var \stdClass $data */

        if ($validated['type'] == 'csv') {
            $instance = new \stdClass();
            if (!empty($data->data)) {
                $instance = $data->data->first();
            }
            $arrayRepresentation = $instance->toArray($request);
            $headers = array_keys($arrayRepresentation);

            // Return transformed and paginated results
            return response()->streamDownload(function () use ($data, $headers, $request) {
                $output = fopen('php://output', 'w');
                fputcsv($output, $headers);

                foreach ($data->data as $row) {
                    $dataRow = $row->toArray($request);
                    array_walk_recursive($dataRow, function(&$value) {
                        $value = str_replace('"', '\\"', $value);
                    });
                    fputcsv($output, $dataRow);
                }

                fclose($output);
            }, 'list-' . str_replace(['/','\\'], '_', strtolower($this->getModelName())) . '.csv');
        }
        //elseif ($validated['type'] == 'json') {

        return response()->json($data->data);
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
