<?php

namespace App\Console\Commands;

use App\Models\Experiment;
use Illuminate\Console\Command;

class SyncExperiments extends Command
{
    protected $signature = 'experiments:sync';
    protected $description = 'Sync experiments from filesystem to database';

    public function handle(): int
    {
        $experimentsDir = base_path('experiments');
        if (!is_dir($experimentsDir)) {
            $this->warn('Experiments directory not found.');
            return Command::SUCCESS;
        }

        $entries = scandir($experimentsDir);
        if ($entries === false) {
            $this->error('Cannot read experiments directory.');
            return Command::FAILURE;
        }

        $created = 0;
        $updated = 0;
        $warnings = [];

        $foundIdentifiers = [];

        foreach ($entries as $entry) {
            if (!preg_match('/^\d{3}-/', $entry)) {
                continue;
            }

            $jsonPath = $experimentsDir . '/' . $entry . '/experiment.json';
            if (!file_exists($jsonPath)) {
                $warnings[] = "Skipping '{$entry}': no experiment.json found";
                continue;
            }

            $json = json_decode(file_get_contents($jsonPath), true);
            if ($json === null) {
                $warnings[] = "Skipping '{$entry}': invalid JSON in experiment.json";
                continue;
            }

            $identifier = $json['identifier'] ?? $entry;
            $foundIdentifiers[] = $identifier;

            $data = [
                'identifier' => $identifier,
                'name' => $json['name'] ?? $entry,
                'description' => $json['description'] ?? null,
                'objective' => $json['objective'] ?? '',
                'hypothesis' => $json['hypothesis'] ?? null,
                'baseline_evaluation_id' => $json['baseline_evaluation_id'] ?? null,
                'evaluation_ids' => $json['evaluation_ids'] ?? [],
                'author' => $json['author'] ?? null,
            ];

            $experiment = Experiment::where('identifier', $identifier)->first();
            if ($experiment) {
                $experiment->update($data);
                $updated++;
            } else {
                Experiment::create($data);
                $created++;
            }

            // Validate evaluation_ids
            $evaluationIds = $json['evaluation_ids'] ?? [];
            foreach ($evaluationIds as $eid) {
                if (!\App\Models\Evaluation::where('id', $eid)->exists()) {
                    $warnings[] = "Experiment '{$identifier}': evaluation ID {$eid} not found in database";
                }
            }
        }

        // Delete experiments whose directories no longer exist
        $deleted = Experiment::whereNotIn('identifier', $foundIdentifiers)->delete();

        $this->info("Created: {$created}, Updated: {$updated}, Deleted: {$deleted}, Warnings: " . count($warnings));

        foreach ($warnings as $warning) {
            $this->warn("  Warning: {$warning}");
        }

        return Command::SUCCESS;
    }
}
