<?php

namespace App\Services;

use App\Models\Evaluation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PdfReportService
{
    public function generate(int $evaluationId): string
    {
        $evaluation = Evaluation::findOrFail($evaluationId);

        $outputPath = Storage::disk('local')->path($evaluation->output_path);
        $pdfPath = $outputPath . '/report.pdf';

        // Cache: return existing file if already generated
        if (file_exists($pdfPath)) {
            return $pdfPath;
        }

        $jsonFile = $outputPath . '/evaluation.json';
        $detailed = [];
        if (file_exists($jsonFile)) {
            $detailed = json_decode(file_get_contents($jsonFile), true) ?? [];
        }

        $params = $evaluation->parameters ?? [];
        $metrics = $evaluation->metrics_summary ?? [];
        $routeMetrics = $detailed['route_metrics'] ?? [];
        $anomalies = $detailed['anomalies'] ?? [];
        $ranking = $detailed['ranking'] ?? [];
        $files = $detailed['files'] ?? [];

        $overviewMapPath = null;
        if (!empty($files['maps']['overview'])) {
            $fullPath = Storage::disk('local')->path($files['maps']['overview']);
            if (file_exists($fullPath)) {
                $overviewMapPath = $fullPath;
            }
        }

        $anomalyMapPath = null;
        if (!empty($files['maps']['anomalies'])) {
            $fullPath = Storage::disk('local')->path($files['maps']['anomalies']);
            if (file_exists($fullPath)) {
                $anomalyMapPath = $fullPath;
            }
        }

        $generatedAt = Carbon::now()->toIso8601String();

        $html = $this->buildHtml(
            $evaluation,
            $params,
            $metrics,
            $routeMetrics,
            $anomalies,
            $ranking,
            $overviewMapPath,
            $anomalyMapPath,
            $generatedAt
        );

        Pdf::setOption('isRemoteEnabled', true);
        $pdf = Pdf::loadHTML($html);
        $pdf->save($pdfPath);

        return $pdfPath;
    }

    private function buildHtml(
        Evaluation $evaluation,
        array $params,
        array $metrics,
        array $routeMetrics,
        array $anomalies,
        array $ranking,
        ?string $overviewMapPath,
        ?string $anomalyMapPath,
        string $generatedAt
    ): string {
        $id = $evaluation->id;
        $date = $evaluation->executed_at ? $evaluation->executed_at->format('d/m/Y H:i:s') : 'N/A';
        $algorithm = $params['algorithm'] ?? 'N/A';
        $algorithmVersion = $params['algorithm_version'] ?? 'N/A';
        $dataset = $params['dataset'] ?? 'N/A';

        $threshold = $params['near_delivery_threshold_km'] ?? 'N/A';
        $ratio = $params['ignored_delivery_ratio'] ?? 'N/A';
        $seed = $params['random_seed'] ?? 'N/A';
        $warehouse = isset($params['warehouse_lat'], $params['warehouse_lng'])
            ? $params['warehouse_lat'] . ', ' . $params['warehouse_lng']
            : 'N/A';

        $execSummary = "
            <div class='metric-grid'>
                <div class='metric-card'><span class='metric-label'>Dist. Promedio</span><span class='metric-value'>" . $this->n($metrics['distancia_promedio_general_km'] ?? null) . " km</span></div>
                <div class='metric-card'><span class='metric-label'>Cobertura</span><span class='metric-value'>" . $this->n($metrics['coverage_territorial_km'] ?? null) . " km</span></div>
                <div class='metric-card'><span class='metric-label'>Anomalías</span><span class='metric-value'>" . ($metrics['total_anomalias_detectadas'] ?? 0) . "</span></div>
                <div class='metric-card'><span class='metric-label'>Penalidad</span><span class='metric-value'>" . $this->n($metrics['operational_penalty_total'] ?? null) . "</span></div>
            </div>";

        $rankingHtml = '';
        if (!empty($ranking)) {
            $rows = '';
            foreach ($ranking as $r) {
                $rows .= "<tr><td>{$r['rank']}</td><td>{$r['route_name']}</td><td>" . $this->n($r['avg_distance_km'] ?? null) . " km</td></tr>";
            }
            $rankingHtml = "
                <h2>Ranking de Rutas</h2>
                <table><thead><tr><th>#</th><th>Ruta</th><th>Prom. Bodega (km)</th></tr></thead><tbody>{$rows}</tbody></table>";
        }

        $routesHtml = '';
        if (!empty($routeMetrics)) {
            $rows = '';
            $cols = ['route_name', 'total_deliveries', 'estimated_route_distance_km', 'avg_distance_to_warehouse_km', 'cluster_radius_km', 'avg_distance_to_centroid_km', 'centroid_to_warehouse_km'];
            $headers = ['Ruta', 'Entregas', 'Dist. Ruta (km)', 'Prom. Bodega (km)', 'Radio (km)', 'Prom. Centroide (km)', 'Centroide→Bodega (km)'];
            foreach ($routeMetrics as $r) {
                $rows .= '<tr>';
                foreach ($cols as $c) {
                    $rows .= '<td>' . $this->n($r[$c] ?? null) . '</td>';
                }
                $rows .= '</tr>';
            }
            $headerRow = '';
            foreach ($headers as $h) {
                $headerRow .= "<th>{$h}</th>";
            }
            $routesHtml = "
                <h2>Métricas por Ruta</h2>
                <table><thead><tr>{$headerRow}</tr></thead><tbody>{$rows}</tbody></table>";
        }

        $anomaliesHtml = '';
        if (!empty($anomalies)) {
            $rows = '';
            foreach ($anomalies as $a) {
                $rows .= "<tr><td>{$a['delivery_id']}</td><td>{$a['route_id']}</td><td>" . $this->n($a['distance_to_warehouse_km'] ?? null) . " km</td><td>" . $this->n($a['centroid_distance_km'] ?? null) . " km</td><td>" . $this->n($a['ratio'] ?? null) . "</td></tr>";
            }
            $anomaliesHtml = "
                <h2>Anomalías Detectadas</h2>
                <table><thead><tr><th>Delivery ID</th><th>Ruta</th><th>Dist. Bodega (km)</th><th>Dist. Centroide (km)</th><th>Ratio</th></tr></thead><tbody>{$rows}</tbody></table>";
        }

        $mapHtml = '';
        if ($overviewMapPath) {
            $imgData = base64_encode(file_get_contents($overviewMapPath));
            $mapHtml .= "<h2>Mapa General</h2><img src='data:image/png;base64,{$imgData}' style='width:100%;max-width:700px;'/>";
        }
        if ($anomalyMapPath) {
            $imgData = base64_encode(file_get_contents($anomalyMapPath));
            $mapHtml .= "<h2>Mapa de Anomalías</h2><img src='data:image/png;base64,{$imgData}' style='width:100%;max-width:700px;'/>";
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #333; }
    h1 { font-size: 18pt; color: #1a56db; border-bottom: 2px solid #1a56db; padding-bottom: 5px; }
    h2 { font-size: 14pt; color: #374151; margin-top: 20px; }
    .cover { text-align: center; padding: 60px 0 40px 0; }
    .cover h1 { font-size: 24pt; border: none; }
    .cover .subtitle { font-size: 14pt; color: #6b7280; margin-top: 10px; }
    .cover .meta { font-size: 10pt; color: #9ca3af; margin-top: 30px; }
    .metric-grid { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 10px; margin: 15px 0; }
    .metric-card { border: 1px solid #e5e7eb; border-radius: 5px; padding: 10px; text-align: center; }
    .metric-label { display: block; font-size: 8pt; color: #6b7280; text-transform: uppercase; }
    .metric-value { display: block; font-size: 14pt; font-weight: bold; margin-top: 3px; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 8pt; }
    th { background: #f3f4f6; text-align: left; padding: 6px 8px; border: 1px solid #d1d5db; }
    td { padding: 4px 8px; border: 1px solid #e5e7eb; }
    tr:nth-child(even) { background: #f9fafb; }
    .params { background: #f3f4f6; padding: 10px; border-radius: 5px; margin: 10px 0; font-size: 9pt; }
    .params table { width: auto; background: transparent; }
    .params td { border: none; padding: 2px 8px; }
    .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #d1d5db; font-size: 7pt; color: #9ca3af; text-align: center; }
    .page-break { page-break-before: always; }
    img { max-width: 100%; height: auto; }
</style>
</head>
<body>

<div class="cover">
    <h1>Reporte de Evaluación #{$id}</h1>
    <div class="subtitle">{$dataset}</div>
    <div class="meta">
        Fecha: {$date}<br>
        Algoritmo: {$algorithm} v{$algorithmVersion}
    </div>
</div>

<h1>Resumen Ejecutivo</h1>
{$execSummary}

<div class="params">
    <table>
        <tr><td><strong>Umbral:</strong></td><td>{$threshold} km</td><td><strong>Ratio:</strong></td><td>{$ratio}</td></tr>
        <tr><td><strong>Semilla:</strong></td><td>{$seed}</td><td><strong>Bodega:</strong></td><td>{$warehouse}</td></tr>
    </table>
</div>

{$rankingHtml}

{$routesHtml}

{$anomaliesHtml}

{$mapHtml}

<div class="footer">
    <p>spec_version: SPEC-004 | evaluation_id: {$id} | algorithm: {$algorithm} | algorithm_version: {$algorithmVersion} | dataset: {$dataset} | generated_at: {$generatedAt}</p>
</div>

</body>
</html>
HTML;
    }

    private function n($value, int $decimals = 2): string
    {
        if ($value === null || $value === '') {
            return '-';
        }
        return number_format((float) $value, $decimals, '.', '');
    }
}
