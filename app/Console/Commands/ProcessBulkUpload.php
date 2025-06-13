<?php

namespace App\Console\Commands;

use App\Modules\Base\Domain\BaseDomain;
use App\Modules\Base\Domain\BulkUpload;
use App\Modules\Base\Infrastructure\Service\AccountManager;
use App\Modules\User\Domain\Identification;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessBulkUpload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bulk-upload:process {--id= : The ID of the specific upload to process} {--lines=30 : Number of lines to process per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process bulk upload CSV files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $uploadId = $this->option('id');
        $linesToProcess = (int) $this->option('lines');

        $identifications = Identification::all();

        foreach ($identifications as $identification) {
            $isConnected = AccountManager::connectToAccount(new Request(), $identification->account);
            if ($isConnected) {
                // Get uploads to process
                $query = BulkUpload::where('status', 'pending')
                    ->orWhere(function ($query) {
                        $query->where('status', 'processing')
                            ->where('processed_rows', '<', \DB::raw('total_rows'));
                    });

                if ($uploadId) {
                    $query->where('id', $uploadId);
                }

                $uploads = $query->get();

                if ($uploads->isEmpty()) {
                    $this->info('No pending uploads to process: ' . $identification->account);
                } else {
                    foreach ($uploads as $upload) {
                        $this->processUpload($upload, $linesToProcess);
                    }
                }
            }
        }

        return 0;
    }

    /**
     * Process a single upload
     *
     * @param BulkUpload $upload
     * @param int $linesToProcess
     * @return void
     */
    protected function processUpload(BulkUpload $upload, int $linesToProcess)
    {
        $this->info("Processing upload ID: {$upload->id} - {$upload->original_filename}");

        // Check if file exists
        if (!Storage::exists($upload->file_path)) {
            $upload->updateStatus('failed', 'File not found');
            $this->error("File not found: {$upload->file_path}");
            return;
        }

        // Update status to processing if it's pending
        if ($upload->status === 'pending') {
            $upload->updateStatus('processing', 'Starting processing');
        }

        try {
            // Open the file
            $filePath = Storage::path($upload->file_path);
            $file = fopen($filePath, 'r');

            if (!$file) {
                $upload->updateStatus('failed', 'Could not open file');
                $this->error("Could not open file: {$upload->file_path}");
                return;
            }

            // Get headers from first line
            $headers = fgetcsv($file);
            if (!$headers) {
                $upload->updateStatus('failed', 'Empty or invalid CSV file');
                $this->error("Empty or invalid CSV file: {$upload->file_path}");
                fclose($file);
                return;
            }

            // Skip to the last processed line
            $currentLine = 1; // Start from line 1 (after headers)
            while ($currentLine <= $upload->last_processed_line) {
                fgetcsv($file);
                $currentLine++;
            }

            // Get model class based on resource name
            $resourceName = str_replace('_', '\\', $upload->resource_name);
            $modelClass = "\\App\\Modules\\{$resourceName}\\Domain\\" . explode('\\', $resourceName)[1];

            // Process lines
            $processedInBatch = 0;
            $failedInBatch = 0;
            $headerMapping = json_decode($upload->header_mapping);
            $hasIdField = isset($headerMapping['id']);
            $idFieldIndex = $hasIdField ? array_search('id', $headerMapping) : null;

            while ($processedInBatch < $linesToProcess && ($row = fgetcsv($file)) !== false) {
                $currentLine++;

                try {
                    // Map CSV data to model fields
                    $data = [];
                    foreach ($headerMapping as $csvHeader => $modelField) {
                        $headerIndex = array_search($csvHeader, $headers);
                        if ($headerIndex !== false && isset($row[$headerIndex])) {
                            $data[$modelField] = $row[$headerIndex];
                        }
                    }

                    // Check if we're updating or creating
                    if ($hasIdField && !empty($row[$idFieldIndex])) {
                        // Update existing record
                        $model = $modelClass::find($row[$idFieldIndex]);
                        if ($model) {
                            $model->update($data);
                            $this->info("Updated record ID: {$row[$idFieldIndex]}");
                        } else {
                            throw new \Exception("Record with ID {$row[$idFieldIndex]} not found");
                        }
                    } else {
                        // Create new record
                        $model = new $modelClass();
                        $model->fill($data);
                        $model->save();
                        $this->info("Created new record ID: {$model->id}");
                    }

                    $processedInBatch++;
                } catch (\Exception $e) {
                    $failedInBatch++;
                    $this->error("Error processing line {$currentLine}: " . $e->getMessage());
                    Log::error("Bulk upload error (ID: {$upload->id}, Line: {$currentLine}): " . $e->getMessage());
                }
            }

            // Update progress
            $newProcessedRows = $upload->processed_rows + $processedInBatch;
            $newFailedRows = $upload->failed_rows + $failedInBatch;
            $upload->updateProgress($newProcessedRows, $newFailedRows, $currentLine - 1);

            // Check if we've processed all rows
            $isComplete = feof($file) || $newProcessedRows + $newFailedRows >= $upload->total_rows;

            if ($isComplete) {
                $upload->updateStatus('completed', 'Processing completed');
                $this->info("Upload ID {$upload->id} completed. Processed: {$newProcessedRows}, Failed: {$newFailedRows}");
            } else {
                $this->info("Batch processed for upload ID {$upload->id}. Processed so far: {$newProcessedRows}, Failed: {$newFailedRows}");
            }

            fclose($file);

        } catch (\Exception $e) {
            $this->error("Error processing upload ID {$upload->id}: " . $e->getMessage());
            $upload->updateStatus('failed', 'Error: ' . $e->getMessage());
            Log::error("Bulk upload error (ID: {$upload->id}): " . $e->getMessage());
        }
    }
}
