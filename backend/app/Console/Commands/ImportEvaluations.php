<?php

namespace App\Console\Commands;

use App\Models\Evaluation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportEvaluations extends Command
{
    protected $signature = 'evaluations:import {--mapping= : JSON file with source→target ID mapping}';
    protected $description = 'Import evaluations from JSON files with explicit IDs';

    public function handle(): int
    {
        $mappingPath = $this->option('mapping');
        if (!$mappingPath) {
            $mappingPath = base_path('experiments/import-mapping.json');
        }

        if (!file_exists($mappingPath)) {
            $this->error("Mapping file not found: {$mappingPath}");
            return Command::FAILURE;
        }

        $mapping = json_decode(file_get_contents($mappingPath), true);
        if ($mapping === null) {
            $this->error('Invalid JSON in mapping file');
            return Command::FAILURE;
        }

        $imported = 0;
        $errors = [];
        $maxId = 0;

        foreach ($mapping as $entry) {
            $sourcePath = $entry['source'] ?? null;
            $targetId = $entry['id'] ?? null;

            if (!$sourcePath || !$targetId) {
                $errors[] = "Missing source or id in mapping entry: " . json_encode($entry);
                continue;
            }

            if (!file_exists($sourcePath)) {
                $errors[] = "Source file not found: {$sourcePath}";
                continue;
            }

            if (Evaluation::where('id', $targetId)->exists()) {
                $this->warn("ID {$targetId} already exists — skipping {$sourcePath}");
                continue;
            }

            $json = json_decode(file_get_contents($sourcePath), true);
            if ($json === null) {
                $errors[] = "Invalid JSON in {$sourcePath}";
                continue;
            }

            $parameters = $json['parameters'] ?? [];
            $metricsSummary = $json['metrics_summary'] ?? [];
            $totalDeliveries = $json['total_deliveries'] ?? count($json['deliveries_flat'] ?? []);
            $totalRoutes = $json['total_routes'] ?? count($json['route_metrics'] ?? []);
            $executedAt = $json['executed_at'] ?? $json['parameters']['executed_at'] ?? now();

            $outputPath = $this->copyArtifacts($json, $targetId, $entry['label'] ?? "eval-{$targetId}");

            // Use explicit executed_at from mapping, or parse directory name, or use now
            if (!empty($entry['executed_at'])) {
                $executedAt = $entry['executed_at'];
            } elseif (!empty($json['executed_at'])) {
                $executedAt = $json['executed_at'];
            } elseif (preg_match('/.*\/(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})/', $sourcePath, $m)) {
                $executedAt = "{$m[1]}-{$m[2]}-{$m[3]} {$m[4]}:{$m[5]}:{$m[6]}";
            } else {
                $executedAt = now();
            }

            try {
                DB::insert(
                    'INSERT INTO evaluations (id, executed_at, parameters, total_deliveries, total_routes, metrics_summary, output_path, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
                    [
                        $targetId,
                        $executedAt,
                        json_encode($parameters),
                        $totalDeliveries,
                        $totalRoutes,
                        json_encode($metricsSummary),
                        $outputPath,
                    ]
                );
                $imported++;
                $maxId = max($maxId, $targetId);
                $label = $entry['label'] ?? $sourcePath;
                $this->info("Imported ID {$targetId}: {$label}");
            } catch (\Exception $e) {
                $errors[] = "Failed to import ID {$targetId}: {$e->getMessage()}";
            }
        }

        // Reset sequence to max id + 1
        if ($maxId > 0) {
            $seq = DB::selectOne("SELECT pg_get_serial_sequence('evaluations', 'id') AS seq");
            if ($seq && $seq->seq) {
                DB::statement("ALTER SEQUENCE {$seq->seq} RESTART WITH " . ($maxId + 1));
                $this->line("Sequence reset to {$maxId}+1");
            }
        }

        $this->newLine();
        $this->info("Imported: {$imported}, Errors: " . count($errors));

        foreach ($errors as $error) {
            $this->warn("  Error: {$error}");
        }

        return Command::SUCCESS;
    }

    private function copyArtifacts(array $json, int $targetId, string $label): string
    {
        $artifactsDir = "experiments/exp001/artifacts/eval-{$targetId}";
        $publicPath = storage_path("app/public/{$artifactsDir}");
        $privatePath = storage_path("app/private/{$artifactsDir}");

        if (!is_dir($publicPath)) {
            mkdir($publicPath, 0755, true);
        }

        // Save full evaluation JSON
        file_put_contents(
            "{$publicPath}/evaluation.json",
            json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        // Save flat deliveries CSV
        if (!empty($json['deliveries_flat'])) {
            $csv = fopen("{$publicPath}/deliveries.csv", 'w');
            $headers = ['delivery_id', 'route_id', 'latitude', 'longitude',
                        'distance_to_warehouse_km', 'distance_to_centroid_km'];
            fputcsv($csv, $headers);
            foreach ($json['deliveries_flat'] as $d) {
                fputcsv($csv, [
                    $d['delivery_id'], $d['route_id'], $d['latitude'], $d['longitude'],
                    $d['distance_to_warehouse_km'], $d['distance_to_centroid_km']
                ]);
            }
            fclose($csv);
        }

        return $artifactsDir;
    }
}
