<?php

namespace App\Http\Controllers;

use App\Exports\MetricsExporter;
use App\Http\Resources\EvaluationResource;
use App\Models\Evaluation;
use App\Models\Experiment;
use App\Services\MeasurementService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EvaluationController extends Controller
{
    private MeasurementService $measurementService;
    private MetricsExporter $metricsExporter;

    public function __construct(MeasurementService $measurementService, MetricsExporter $metricsExporter)
    {
        $this->measurementService = $measurementService;
        $this->metricsExporter = $metricsExporter;
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'near_delivery_threshold_km' => 'sometimes|numeric|min:0.001',
            'ignored_delivery_ratio' => 'sometimes|numeric|min:1.0001',
            'random_seed' => 'sometimes|integer|nullable',
            'algorithm' => 'sometimes|string|max:100',
            'algorithm_version' => 'sometimes|string|max:20',
            'distance_mode' => 'sometimes|in:geodesic,vial',
        ]);

        $parameters = array_merge([
            'near_delivery_threshold_km' => 1.0,
            'ignored_delivery_ratio' => 2.0,
            'random_seed' => null,
            'algorithm' => 'unknown',
            'algorithm_version' => '1.0',
            'distance_mode' => config('evaluation.distance_mode'),
        ], $validated);

        $warehouse = $this->measurementService->loadWarehouse();
        $parameters['warehouse_lat'] = $warehouse['lat'];
        $parameters['warehouse_lng'] = $warehouse['lng'];
        $parameters['dataset'] = 'Valparaíso Demo';

        $timestamp = Carbon::now()->format('Ymd_His') . '_' . \Illuminate\Support\Str::random(6);
        $outputDir = 'evaluations/' . $timestamp;
        $outputPath = Storage::disk('local')->path($outputDir);

        $result = $this->measurementService->execute($parameters, $outputPath);

        $files = [];

        $result['files'] = [
            'json' => $outputDir . '/evaluation.json',
            'csv' => $outputDir . '/evaluation.csv',
            'deliveries_csv' => $outputDir . '/deliveries.csv',
        ];

        $mapFiles = $result['map_files'] ?? [];
        $result['files']['maps'] = [
            'overview' => !empty($mapFiles['overview']) ? $outputDir . '/' . $mapFiles['overview'] : null,
            'routes' => array_map(fn($f) => $outputDir . '/' . $f, $mapFiles['routes'] ?? []),
            'anomalies' => !empty($mapFiles['anomalies']) ? $outputDir . '/' . $mapFiles['anomalies'] : null,
        ];

        $jsonPath = $outputPath . '/evaluation.json';
        $this->metricsExporter->exportJson($result, $jsonPath);
        $files['json'] = $outputDir . '/evaluation.json';

        $csvPath = $outputPath . '/evaluation.csv';
        $this->metricsExporter->exportCsv($result['route_metrics'], $csvPath);
        $files['csv'] = $outputDir . '/evaluation.csv';

        $deliveriesFlat = $result['deliveries_flat'] ?? [];
        $deliveriesPath = $outputPath . '/deliveries.csv';
        $this->metricsExporter->exportDeliveriesCsv($deliveriesFlat, $deliveriesPath);
        $files['deliveries_csv'] = $outputDir . '/deliveries.csv';

        $result['metrics_summary']['execution_time_sec'] = $result['execution_time_sec'] ?? 0;
        $result['metrics_summary']['mode'] = $parameters['distance_mode'];
        $metricsSummary = $result['metrics_summary'];

        $evaluation = Evaluation::create([
            'executed_at' => Carbon::now(),
            'parameters' => $parameters,
            'total_deliveries' => $result['total_deliveries'],
            'total_routes' => $result['total_routes'],
            'metrics_summary' => $metricsSummary,
            'output_path' => $outputDir,
        ]);

        $response = array_merge(
            (new EvaluationResource($evaluation))->toArray($request),
            [
                'mode' => $parameters['distance_mode'],
                'execution_time_sec' => $result['execution_time_sec'],
                'route_metrics' => $result['route_metrics'],
                'anomalies' => $result['anomalies'],
                'ranking' => $result['ranking'],
                'files' => $files,
            ]
        );

        return response()->json($response, 201);
    }

    public function index(): JsonResponse
    {
        $evaluations = Evaluation::orderBy('executed_at', 'desc')
            ->get(['id', 'executed_at', 'total_deliveries', 'total_routes', 'metrics_summary']);

        return response()->json([
            'data' => EvaluationResource::collection($evaluations),
        ]);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $evaluation = Evaluation::findOrFail($id);

        $outputPath = Storage::disk('local')->path($evaluation->output_path);
        $jsonFile = $outputPath . '/evaluation.json';

        $detailed = [];
        if (file_exists($jsonFile)) {
            $detailed = json_decode(file_get_contents($jsonFile), true) ?? [];
        }

        $evaluation->setRelation('detailedMetrics', $detailed);

        $experiment = Experiment::whereJsonContains('evaluation_ids', $id)->first(['id', 'identifier', 'name']);

        $response = (new EvaluationResource($evaluation))->toArray($request);
        if ($experiment) {
            $response['experiment'] = $experiment->toArray();
        }

        return response()->json($response);
    }

    public function file(int $id, string $filename)
    {
        $evaluation = Evaluation::findOrFail($id);

        $allowedExtensions = ['json', 'csv', 'png'];
        $sanitized = basename($filename);

        $ext = strtolower(pathinfo($sanitized, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions, true)) {
            abort(404);
        }

        $filePath = Storage::disk('local')->path($evaluation->output_path . '/' . $sanitized);
        if (!file_exists($filePath)) {
            abort(404);
        }

        $mimeTypes = [
            'json' => 'application/json',
            'csv' => 'text/csv',
            'png' => 'image/png',
        ];

        return response()->file($filePath, [
            'Content-Type' => $mimeTypes[$ext] ?? 'application/octet-stream',
        ]);
    }

    public function pdf(int $id)
    {
        $evaluation = Evaluation::findOrFail($id);

        $pdfPath = app(\App\Services\PdfReportService::class)->generate($id);

        return response()->file($pdfPath, [
            'Content-Type' => 'application/pdf',
        ]);
    }

}
