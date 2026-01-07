<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessProductImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $chunkSize = 1000;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $fullPath = Storage::path($this->filePath);
            
            if (!file_exists($fullPath)) {
                throw new \Exception("File not found: {$fullPath}");
            }

            Log::info('Starting product import', ['file' => $this->filePath]);

            $this->processCsvFile($fullPath);

            Log::info('Product import completed successfully', ['file' => $this->filePath]);
        } catch (\Exception $e) {
            Log::error('Product import failed', [
                'file' => $this->filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        } finally {
            // Clean up the uploaded file
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }
        }
    }

    /**
     * Process CSV file in chunks
     */
    protected function processCsvFile(string $filePath): void
    {
        $handle = fopen($filePath, 'r');
        
        if (!$handle) {
            throw new \Exception("Could not open file: {$filePath}");
        }

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            throw new \Exception("Could not read CSV headers");
        }

        // Normalize headers (trim and lowercase)
        $headers = array_map(function($header) {
            return strtolower(trim($header));
        }, $headers);

        $rowCount = 0;
        $processedCount = 0;
        $skippedCount = 0;
        $batch = [];

        // Process file in chunks
        while (($row = fgetcsv($handle)) !== false) {
            $rowCount++;

            // Skip empty rows
            if (empty(array_filter($row))) {
                $skippedCount++;
                continue;
            }

            // Map row data to associative array
            $data = [];
            foreach ($headers as $index => $header) {
                $data[$header] = $row[$index] ?? null;
            }

            // Validate and prepare product data
            $productData = $this->prepareProductData($data);
            
            if ($productData === null) {
                $skippedCount++;
                continue;
            }

            $batch[] = $productData;

            // Insert in batches
            if (count($batch) >= $this->chunkSize) {
                $this->insertBatch($batch);
                $processedCount += count($batch);
                $batch = [];
                
                Log::info("Processed {$processedCount} products so far...");
            }
        }

        // Insert remaining batch
        if (!empty($batch)) {
            $this->insertBatch($batch);
            $processedCount += count($batch);
        }

        fclose($handle);

        Log::info('Import summary', [
            'total_rows' => $rowCount,
            'processed' => $processedCount,
            'skipped' => $skippedCount
        ]);
    }

    /**
     * Prepare product data from CSV row
     */
    protected function prepareProductData(array $row): ?array
    {
        // Validate required fields
        if (empty($row['name']) || !isset($row['price'])) {
            return null;
        }

        return [
            'name' => trim($row['name'] ?? ''),
            'description' => !empty($row['description']) ? trim($row['description']) : null,
            'price' => $this->parsePrice($row['price'] ?? 0),
            'image' => !empty($row['image']) ? trim($row['image']) : 'products/default.png',
            'category' => !empty($row['category']) ? trim($row['category']) : null,
            'stock' => (int) ($row['stock'] ?? 0),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Parse price value
     */
    protected function parsePrice($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        // Remove currency symbols and commas
        $cleaned = preg_replace('/[^0-9.]/', '', (string) $value);
        return (float) $cleaned;
    }

    /**
     * Insert batch of products
     */
    protected function insertBatch(array $batch): void
    {
        try {
            DB::table('products')->insert($batch);
        } catch (\Exception $e) {
            Log::error('Batch insert failed', [
                'batch_size' => count($batch),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

