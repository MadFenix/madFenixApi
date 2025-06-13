<?php

namespace App\Modules\Base\Transformers;

use App\Modules\Base\Domain\BulkUpload as BulkUploadModel;

class BulkUpload extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|BulkUploadModel
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'account' => $this->account,
            'resource_name' => $this->resource_name,
            'original_filename' => $this->original_filename,
            'header_mapping' => $this->header_mapping,
            'status' => $this->status,
            'status_info' => $this->status_info,
            'total_rows' => $this->total_rows,
            'processed_rows' => $this->processed_rows,
            'failed_rows' => $this->failed_rows,
            'progress_percentage' => $this->total_rows > 0
                ? round(($this->processed_rows / $this->total_rows) * 100, 2)
                : 0,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
