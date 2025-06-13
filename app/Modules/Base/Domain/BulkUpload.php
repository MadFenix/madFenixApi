<?php

namespace App\Modules\Base\Domain;

use Illuminate\Database\Eloquent\Model;

class BulkUpload extends Model
{
    protected $table = 'bulk_uploads';

    protected $fillable = [
        'account',
        'resource_name',
        'file_path',
        'original_filename',
        'header_mapping',
        'status',
        'status_info',
        'total_rows',
        'processed_rows',
        'failed_rows',
        'last_processed_line',
    ];

    protected $casts = [
        'header_mapping' => 'array',
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'failed_rows' => 'integer',
        'last_processed_line' => 'integer',
    ];

    /**
     * Update the status and status_info of the bulk upload
     *
     * @param string $status
     * @param string|null $statusInfo
     * @return bool
     */
    public function updateStatus(string $status, ?string $statusInfo = null): bool
    {
        $this->status = $status;
        if ($statusInfo !== null) {
            $this->status_info = $statusInfo;
        }
        return $this->save();
    }

    /**
     * Update the progress of the bulk upload
     *
     * @param int $processedRows
     * @param int $failedRows
     * @param int $lastProcessedLine
     * @return bool
     */
    public function updateProgress(int $processedRows, int $failedRows, int $lastProcessedLine): bool
    {
        $this->processed_rows = $processedRows;
        $this->failed_rows = $failedRows;
        $this->last_processed_line = $lastProcessedLine;
        return $this->save();
    }
}
