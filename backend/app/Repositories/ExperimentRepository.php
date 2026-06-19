<?php

namespace App\Repositories;

use App\Models\Experiment;

class ExperimentRepository
{
    public function getReportContent(Experiment $experiment): ?string
    {
        $path = $this->getPath($experiment) . '/report.md';
        return file_exists($path) ? file_get_contents($path) : null;
    }

    public function getAssets(Experiment $experiment): array
    {
        $assetsDir = $this->getPath($experiment) . '/assets';
        if (!is_dir($assetsDir)) {
            return [];
        }

        $files = scandir($assetsDir);
        if ($files === false) {
            return [];
        }

        $allowedExtensions = ['png', 'json', 'csv', 'pdf', 'md'];
        $assets = [];
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, $allowedExtensions, true)) {
                $assets[] = $file;
            }
        }

        return $assets;
    }

    public function getAssetPath(Experiment $experiment, string $filename): ?string
    {
        $sanitized = basename($filename);
        if ($sanitized !== $filename || str_contains($filename, '..')) {
            return null;
        }

        $allowedExtensions = ['png', 'json', 'csv', 'pdf', 'md'];
        $ext = strtolower(pathinfo($sanitized, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions, true)) {
            return null;
        }

        $path = $this->getPath($experiment) . '/assets/' . $sanitized;
        return file_exists($path) ? $path : null;
    }

    public function getReportPdfPath(Experiment $experiment): ?string
    {
        $path = $this->getPath($experiment) . '/report.pdf';
        return file_exists($path) ? $path : null;
    }

    public function getPath(Experiment $experiment): string
    {
        return base_path('experiments/' . $experiment->identifier);
    }
}
