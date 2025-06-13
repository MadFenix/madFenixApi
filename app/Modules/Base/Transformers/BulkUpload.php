<?php

namespace App\Modules\Base\Transformers;

use App\Modules\Base\Domain\BulkUpload as BulkUploadModel;
use League\Fractal\TransformerAbstract;

class BulkUpload extends TransformerAbstract
{
    /**
     * Transform the BulkUpload entity
     *
     * @param BulkUploadModel $bulkUpload
     * @return array
     */
    public function transform(BulkUploadModel $bulkUpload): array
    {
        return [
            'id' => $bulkUpload->id,
            'account' => $bulkUpload->account,
            'resource_name' => $bulkUpload->resource_name,
            'original_filename' => $bulkUpload->original_filename,
            'header_mapping' => $bulkUpload->header_mapping,
            'status' => $bulkUpload->status,
            'status_info' => $bulkUpload->status_info,
            'total_rows' => $bulkUpload->total_rows,
            'processed_rows' => $bulkUpload->processed_rows,
            'failed_rows' => $bulkUpload->failed_rows,
            'progress_percentage' => $bulkUpload->total_rows > 0
                ? round(($bulkUpload->processed_rows / $bulkUpload->total_rows) * 100, 2)
                : 0,
            'created_at' => $bulkUpload->created_at->toIso8601String(),
            'updated_at' => $bulkUpload->updated_at->toIso8601String(),
        ];
    }
}
