<?php

namespace App\Services;

use App\Models\UnifiedMilestone;
use App\Models\UnifiedMetric;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleSheetsService
{
    protected string $spreadsheetId;
    protected string $apiKey;

    public function __construct()
    {
        $this->spreadsheetId = env('GOOGLE_SPREADSHEET_ID', '1OXmqaEgWczJ3um8zwRJfqjOp6Zklc_eSvvW687L57Y4');
        $this->apiKey = env('GOOGLE_DEVELOPER_KEY', 'AIzaSyAlyG7fF2gElxATHS9URW7YY-1TQSlvnmg');
    }

    /**
     * Fetch and sync all sheets/tabs from Google Sheets.
     *
     * @return array
     * @throws Exception
     */
    public function sync(): array
    {
        if (empty($this->spreadsheetId)) {
            throw new Exception("Google Spreadsheet ID is not configured.");
        }

        if (empty($this->apiKey)) {
            throw new Exception("Google Developer Key is not configured.");
        }

        Log::info("Starting Google Sheets synchronization for Spreadsheet ID: {$this->spreadsheetId}");

        $milestones = $this->fetchSheetData('unified_milestones');
        $metrics = $this->fetchSheetData('unified_metrics');

        DB::beginTransaction();
        try {
            // 1. Sync milestones
            UnifiedMilestone::query()->delete();
            $milestoneCount = 0;
            foreach ($milestones as $row) {
                if (empty($row['activity_id']) && empty($row['kegiatan'])) {
                    continue;
                }

                $attributes = [];
                if (!empty($row['attributes_json'])) {
                    $attributes = json_decode($row['attributes_json'], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::warning("Failed to parse attributes_json for milestone: " . json_encode($row));
                        $attributes = ['raw_attributes' => $row['attributes_json']];
                    }
                }

                UnifiedMilestone::create([
                    'activity_id' => $row['activity_id'] ?? null,
                    'kategori' => $row['kategori'] ?? null,
                    'tanggal' => $row['tanggal'] ?? null,
                    'kegiatan' => $row['kegiatan'] ?? null,
                    'status' => $row['status'] ?? null,
                    'pic' => $row['pic'] ?? null,
                    'attributes_json' => $attributes,
                ]);
                $milestoneCount++;
            }

            // 2. Sync metrics
            UnifiedMetric::query()->delete();
            $metricCount = 0;
            foreach ($metrics as $row) {
                if (empty($row['activity_id']) && empty($row['metric_id'])) {
                    continue;
                }

                $context = [];
                if (!empty($row['context_json'])) {
                    $context = json_decode($row['context_json'], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::warning("Failed to parse context_json for metric: " . json_encode($row));
                        $context = ['raw_context' => $row['context_json']];
                    }
                }

                UnifiedMetric::create([
                    'activity_id' => $row['activity_id'] ?? null,
                    'metric_id' => $row['metric_id'] ?? null,
                    'label' => $row['label'] ?? null,
                    'target' => $row['target'] ?? null,
                    'completed' => $row['completed'] ?? null,
                    'worked' => $row['worked'] ?? null,
                    'percentage' => $row['percentage'] ?? null,
                    'context_json' => $context,
                ]);
                $metricCount++;
            }

            DB::commit();
            Log::info("Google Sheets synchronization completed successfully. Milestones: {$milestoneCount}, Metrics: {$metricCount}");

            return [
                'success' => true,
                'milestones_count' => $milestoneCount,
                'metrics_count' => $metricCount,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Google Sheets synchronization failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch sheet range values using Google Sheets API V4.
     *
     * @param string $sheetName
     * @return array
     * @throws Exception
     */
    protected function fetchSheetData(string $sheetName): array
    {
        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$this->spreadsheetId}/values/{$sheetName}?key={$this->apiKey}";
        
        $response = Http::timeout(10)->get($url);

        if (!$response->successful()) {
            $errorMsg = $response->json('error.message') ?? "Unknown Google API error.";
            throw new Exception("Failed to fetch sheet data for '{$sheetName}': {$errorMsg}");
        }

        $values = $response->json('values');
        if (empty($values)) {
            return [];
        }

        $headers = array_shift($values);
        $headers = array_map('trim', $headers);

        $mappedData = [];
        foreach ($values as $row) {
            // Pad row if it has fewer elements than headers
            $paddedRow = array_pad($row, count($headers), null);
            // Slice row if it has more elements than headers
            $paddedRow = array_slice($paddedRow, 0, count($headers));
            
            $mappedData[] = array_combine($headers, $paddedRow);
        }

        return $mappedData;
    }
}
