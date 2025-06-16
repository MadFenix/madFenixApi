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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use App\Modules\Base\Domain\BulkUpload;

abstract class ResourceController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function getNameParameter(): string
    {
        return 'name';
    }

    protected function getParentIdentificator()
    {
        return null;
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
            'sorting' => 'nullable|string',
            'parent_id' => 'nullable|integer'
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
        $parent_id = $validated['parent_id'] ?? null;
        $sorting = $validated['sorting'] ?? 'created_at:desc';
        list($sortColumn, $sortDirection) = explode(':', $sorting);

        // The main model query
        $query = ($this->getModelClass())::orderBy($sortColumn, $sortDirection);

        // Apply parent id
        if ($this->getParentIdentificator() && is_numeric($parent_id)) {
            $query = $query->where($this->getParentIdentificator(), '=', $parent_id);
        }

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
                    array_walk($dataRow, function(&$value) {
                        if (is_array($value) || is_object($value)) {
                            $value = json_encode($value);
                        }

                        $value = str_replace(['"',','], ['\\"','*-*'], $value);
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

    /**
     * Upload a CSV file for bulk processing
     *
     * @param string $account
     * @return JsonResponse
     */
    public function upload($account)
    {
        try {
            $request = request();

            // Validate request
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
                'header_mapping' => 'required',
            ]);

            if ($validator->fails()) {
                throw ValidationException::withMessages($validator->errors()->toArray());
            }

            $file = $request->file('file');
            $headerMapping = $request->input('header_mapping');

            // Get model name for the resource
            $modelName = $this->getModelName();
            $modelClassName = $this->getModelClass();
            $resourceNameToUploadDir = str_replace('\\', '_', $modelName);
            $resourceName = str_replace('\\', '_', $modelClassName);

            // Create upload directory if it doesn't exist
            $uploadDir = 'uploads/' . $account . '/' . $resourceNameToUploadDir;
            if (!Storage::exists($uploadDir)) {
                Storage::makeDirectory($uploadDir);
            }

            // Generate unique filename
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = $uploadDir . '/' . $filename;

            // Store file
            Storage::putFileAs($uploadDir, $file, $filename);

            // Count total rows in CSV (excluding header)
            $totalRows = 0;
            if (($handle = fopen(Storage::path($filePath), 'r')) !== false) {
                // Skip header row
                fgetcsv($handle);

                while (fgetcsv($handle) !== false) {
                    $totalRows++;
                }
                fclose($handle);
            }

            // Create bulk upload record
            $bulkUpload = new BulkUpload();
            $bulkUpload->account = $account;
            $bulkUpload->resource_name = $resourceName;
            $bulkUpload->file_path = $filePath;
            $bulkUpload->original_filename = $file->getClientOriginalName();
            $bulkUpload->header_mapping = $headerMapping;
            $bulkUpload->status = 'pending';
            $bulkUpload->total_rows = $totalRows;
            $bulkUpload->save();

            return response()->json([
                'message' => 'File uploaded successfully and queued for processing',
                'upload_id' => $bulkUpload->id,
                'total_rows' => $totalRows,
            ]);

        } catch (\Throwable $th) {
            return $this->formatExceptionError($th);
        }
    }

    /**
     * Get the status of a bulk upload
     *
     * @param string $account
     * @return JsonResponse
     */
    public function uploadStatus($account)
    {
        try {
            // Get model name for the resource
            $modelClassName = $this->getModelClass();
            $resourceName = str_replace('\\', '_', $modelClassName);

            $bulkUploads = BulkUpload::where('account', $account)
                ->where(function ($query) {
                    $query->where('status', 'pending')
                        ->orWhere('status', 'processing');
                })
                ->where('resource_name', $resourceName)
                ->get();

            return response()->json(\App\Modules\Base\Transformers\BulkUpload::collection($bulkUploads));
        } catch (\Throwable $th) {
            return $this->formatExceptionError($th);
        }
    }

    /**
     * Delete a bulk upload
     *
     * @param string $account
     * @param int $id
     * @return JsonResponse
     */
    public function deleteUpload($account, $id)
    {
        try {
            $bulkUpload = BulkUpload::where('account', $account)
                ->where('id', $id)
                ->firstOrFail();

            // Delete the file if it exists
            if (Storage::exists($bulkUpload->file_path)) {
                Storage::delete($bulkUpload->file_path);
            }

            // Delete the record
            $bulkUpload->delete();

            return response()->json([
                'message' => 'Bulk upload deleted successfully'
            ]);

        } catch (\Throwable $th) {
            return $this->formatExceptionError($th);
        }
    }

    /**
     * List the fields of the domain model
     *
     * @param string $account
     * @return JsonResponse
     */
    public function fields($account)
    {
        try {
            $modelClass = $this->getModelClass();
            $model = new $modelClass();

            // Get table columns using Schema
            $table = $model->getTable();
            $columns = \Schema::getColumnListing($table);

            $fields = [];
            foreach ($columns as $column) {
                $type = \Schema::getColumnType($table, $column);
                $fields[$column] = [
                    'name' => $column,
                    'type' => $type
                ];
            }

            // Add fillable property if defined
            if (property_exists($model, 'fillable') && is_array($model->fillable)) {
                foreach ($model->fillable as $fillable) {
                    if (isset($fields[$fillable])) {
                        $fields[$fillable]['fillable'] = true;
                    }
                }
            }

            return response()->json([
                'model' => class_basename($modelClass),
                'table' => $table,
                'fields' => $fields
            ]);

        } catch (\Throwable $th) {
            return $this->formatExceptionError($th);
        }
    }
}
