<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExperimentResource;
use App\Models\Evaluation;
use App\Models\Experiment;
use App\Repositories\ExperimentRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExperimentController extends Controller
{
    private ExperimentRepository $experimentRepository;

    public function __construct(ExperimentRepository $experimentRepository)
    {
        $this->experimentRepository = $experimentRepository;
    }

    public function index(): JsonResponse
    {
        $experiments = Experiment::orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => ExperimentResource::collection($experiments),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $experiment = Experiment::findOrFail($id);

        $evaluations = Evaluation::whereIn('id', $experiment->evaluation_ids ?? [])->get();

        return response()->json(
            (new ExperimentResource($experiment))->resolvedEvaluations($evaluations->all())
        );
    }

    public function report(int $id)
    {
        $experiment = Experiment::findOrFail($id);
        $content = $this->experimentRepository->getReportContent($experiment);

        if ($content === null) {
            abort(404, 'Report not found');
        }

        return response($content, 200, [
            'Content-Type' => 'text/markdown',
        ]);
    }

    public function reportPdf(int $id)
    {
        $experiment = Experiment::findOrFail($id);

        $path = $this->experimentRepository->getReportPdfPath($experiment);
        if ($path !== null) {
            return response()->file($path, ['Content-Type' => 'application/pdf']);
        }

        $markdown = $this->experimentRepository->getReportContent($experiment);
        if ($markdown === null) {
            abort(404, 'Report not found');
        }

        $html = view('pdfs.experiment-report', [
            'experiment' => $experiment,
            'content' => Str::markdown($markdown),
        ])->render();

        $pdf = Pdf::loadHTML($html);
        return $pdf->download("reporte-{$experiment->identifier}.pdf");
    }

    public function asset(int $id, string $filename)
    {
        $experiment = Experiment::findOrFail($id);
        $path = $this->experimentRepository->getAssetPath($experiment, $filename);

        if ($path === null) {
            abort(404);
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeTypes = [
            'png' => 'image/png',
            'json' => 'application/json',
            'csv' => 'text/csv',
            'pdf' => 'application/pdf',
            'md' => 'text/markdown',
        ];

        return response()->file($path, [
            'Content-Type' => $mimeTypes[$ext] ?? 'application/octet-stream',
        ]);
    }
}
