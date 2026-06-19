'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import { evaluationsApi, Evaluation, RouteMetric, Anomaly } from '@/lib/api';

function n(v: number | undefined | null, decimals = 2): string {
  if (v === undefined || v === null) return '-';
  return v.toLocaleString('es-CL', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
}

function MetricCard({ label, value, unit, color }: { label: string; value: string; unit?: string; color: string }) {
  return (
    <div className="bg-white rounded border shadow-sm p-4">
      <div className="flex items-center gap-3">
        <div className={`w-10 h-10 rounded ${color} flex items-center justify-center text-white font-bold text-sm`}>
          {value.split(' ')[0]}
        </div>
        <div>
          <div className="text-xs text-gray-500 uppercase tracking-wide">{label}</div>
          <div className="text-lg font-semibold">{value}{unit && <span className="text-sm text-gray-400 ml-1">{unit}</span>}</div>
        </div>
      </div>
    </div>
  );
}

function ParametersCard({ params }: { params: Evaluation['parameters'] }) {
  if (!params) return null;
  const rows: [string, string][] = [
    ['Umbral cercanía (km)', String(params.near_delivery_threshold_km)],
    ['Ratio ignorado', String(params.ignored_delivery_ratio)],
    ['Random Seed', params.random_seed !== null ? String(params.random_seed) : 'N/A'],
    ['Algoritmo', `${params.algorithm} v${params.algorithm_version}`],
    ['Bodega', `${params.warehouse_lat}, ${params.warehouse_lng}`],
    ['Dataset', params.dataset],
  ];
  return (
    <div className="bg-white rounded border shadow-sm p-4">
      <h3 className="text-sm font-semibold text-gray-700 mb-2">Parámetros</h3>
      <table className="text-xs w-full">
        <tbody>
          {rows.map(([k, v]) => (
            <tr key={k} className="border-b border-gray-100">
              <td className="py-1 pr-4 text-gray-500 w-40">{k}</td>
              <td className="py-1 font-mono">{v}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

function RouteMetricsTable({ routes }: { routes: RouteMetric[] }) {
  const [sortCol, setSortCol] = useState<string>('avg_distance_to_warehouse_km');
  const [sortAsc, setSortAsc] = useState(true);

  const sorted = [...routes].sort((a, b) => {
    const va = (a as any)[sortCol] ?? 0;
    const vb = (b as any)[sortCol] ?? 0;
    return sortAsc ? va - vb : vb - va;
  });

  const toggleSort = (col: string) => {
    if (sortCol === col) setSortAsc(!sortAsc);
    else { setSortCol(col); setSortAsc(true); }
  };

  const SortHeader = ({ col, children }: { col: string; children: React.ReactNode }) => (
    <th className="text-left py-2 px-3 text-xs font-medium text-gray-500 cursor-pointer hover:text-gray-700" onClick={() => toggleSort(col)}>
      {children} {sortCol === col ? (sortAsc ? '▲' : '▼') : ''}
    </th>
  );

  return (
    <div className="bg-white rounded border shadow-sm overflow-x-auto">
      <table className="w-full text-sm">
        <thead className="bg-gray-50 border-b">
          <tr>
            <SortHeader col="route_name">Ruta</SortHeader>
            <SortHeader col="total_deliveries">Entregas</SortHeader>
            <SortHeader col="estimated_route_distance_km">Dist. Ruta (km)</SortHeader>
            <SortHeader col="avg_distance_to_warehouse_km">Prom. Bodega (km)</SortHeader>
            <SortHeader col="min_distance_to_warehouse_km">Min (km)</SortHeader>
            <SortHeader col="max_distance_to_warehouse_km">Max (km)</SortHeader>
            <SortHeader col="cluster_radius_km">Radio (km)</SortHeader>
            <SortHeader col="avg_distance_to_centroid_km">Prom. Centroide (km)</SortHeader>
            <SortHeader col="centroid_to_warehouse_km">Centroide→Bodega (km)</SortHeader>
          </tr>
        </thead>
        <tbody>
          {sorted.map(r => (
            <tr key={r.route_id} className="border-b border-gray-100 hover:bg-gray-50">
              <td className="py-2 px-3 font-medium">{r.route_name}</td>
              <td className="py-2 px-3">{r.total_deliveries}</td>
              <td className="py-2 px-3 font-mono">{n(r.estimated_route_distance_km)}</td>
              <td className="py-2 px-3 font-mono">{n(r.avg_distance_to_warehouse_km)}</td>
              <td className="py-2 px-3 font-mono">{n(r.min_distance_to_warehouse_km)}</td>
              <td className="py-2 px-3 font-mono">{n(r.max_distance_to_warehouse_km)}</td>
              <td className="py-2 px-3 font-mono">{n(r.cluster_radius_km)}</td>
              <td className="py-2 px-3 font-mono">{n(r.avg_distance_to_centroid_km)}</td>
              <td className="py-2 px-3 font-mono">{n(r.centroid_to_warehouse_km)}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

function AnomaliesTable({ anomalies }: { anomalies: Anomaly[] }) {
  if (!anomalies || anomalies.length === 0) return null;
  return (
    <div className="bg-white rounded border shadow-sm overflow-x-auto">
      <div className="px-4 py-3 border-b bg-red-50">
        <h3 className="text-sm font-semibold text-red-700">{anomalies.length} Anomalías Detectadas</h3>
      </div>
      <table className="w-full text-sm">
        <thead className="bg-gray-50 border-b">
          <tr>
            <th className="text-left py-2 px-3 text-xs font-medium text-gray-500">Delivery ID</th>
            <th className="text-left py-2 px-3 text-xs font-medium text-gray-500">Ruta</th>
            <th className="text-left py-2 px-3 text-xs font-medium text-gray-500">Dist. Bodega (km)</th>
            <th className="text-left py-2 px-3 text-xs font-medium text-gray-500">Dist. Centroide (km)</th>
            <th className="text-left py-2 px-3 text-xs font-medium text-gray-500">Ratio</th>
          </tr>
        </thead>
        <tbody>
          {anomalies.map(a => (
            <tr key={a.delivery_id} className="border-b border-gray-100 hover:bg-red-50">
              <td className="py-2 px-3 font-mono">{a.delivery_id}</td>
              <td className="py-2 px-3">Ruta {a.route_id}</td>
              <td className="py-2 px-3 font-mono">{n(a.distance_to_warehouse_km)}</td>
              <td className="py-2 px-3 font-mono">{n(a.centroid_distance_km)}</td>
              <td className="py-2 px-3 font-mono text-red-600 font-semibold">{n(a.ratio)}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

function Rankings({ evaluation }: { evaluation: Evaluation }) {
  if (!evaluation.ranking || evaluation.ranking.length === 0) return null;
  const best = evaluation.ranking[0];
  return (
    <div className="bg-white rounded border shadow-sm p-4">
      <h3 className="text-sm font-semibold text-gray-700 mb-3">Ranking de Rutas</h3>
      <div className="space-y-1 text-sm">
        {evaluation.ranking.map(r => (
          <div key={r.rank} className={`flex items-center justify-between py-1 px-2 rounded ${r.rank === 1 ? 'bg-green-50 font-medium' : ''}`}>
            <div className="flex items-center gap-2">
              <span className={`w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold text-white ${r.rank <= 3 ? 'bg-blue-500' : 'bg-gray-300'}`}>{r.rank}</span>
              <span>{r.route_name}</span>
            </div>
            <span className="font-mono text-gray-600">{n(r.avg_distance_km)} km</span>
          </div>
        ))}
      </div>
      {best && (
        <div className="mt-3 text-xs text-green-700 bg-green-50 rounded p-2">
          Mejor ruta: <strong>{best.route_name}</strong> — {n(best.avg_distance_km)} km promedio desde bodega
        </div>
      )}
    </div>
  );
}

function MapsSection({ evaluation }: { evaluation: Evaluation }) {
  const files = evaluation.files;
  if (!files?.maps) return null;

  const img = (filename: string | null, label: string) => {
    if (!filename) return null;
    const basename = filename.split('/').pop();
    if (!basename) return null;
    return (
      <div className="bg-white rounded border shadow-sm overflow-hidden">
        <div className="px-4 py-2 border-b bg-gray-50 text-sm font-medium text-gray-700">{label}</div>
        <img
          src={evaluationsApi.fileUrl(evaluation.id, basename)}
          alt={label}
          className="w-full"
          style={{ maxHeight: 500, objectFit: 'contain' }}
        />
      </div>
    );
  };

  return (
    <div className="space-y-4">
      <h2 className="text-lg font-semibold">Mapas Generados</h2>
      <div className="grid grid-cols-1 gap-4">
        {img(files.maps.overview, 'Vista General')}
        {img(files.maps.anomalies, 'Mapa de Anomalías')}
      </div>
      {files.maps.routes.length > 0 && (
        <div>
          <h3 className="text-sm font-semibold text-gray-700 mb-2">Mapas por Ruta</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {files.maps.routes.map((path, i) => img(path, `Ruta ${i + 1}`)).filter(Boolean)}
          </div>
        </div>
      )}
    </div>
  );
}

function DownloadsSection({ evaluation }: { evaluation: Evaluation }) {
  const files = evaluation.files;
  if (!files) return null;

  const dl = (path: string | undefined | null, label: string) => {
    if (!path) return null;
    const basename = path.split('/').pop();
    if (!basename) return null;
    return (
      <a
        href={evaluationsApi.fileUrl(evaluation.id, basename)}
        target="_blank"
        rel="noopener noreferrer"
        className="text-blue-600 hover:text-blue-800 text-sm underline"
      >
        {label}
      </a>
    );
  };

  return (
    <div className="bg-white rounded border shadow-sm p-4">
      <h3 className="text-sm font-semibold text-gray-700 mb-2">Descargas</h3>
      <div className="flex flex-wrap gap-4">
        {dl(files.json, 'JSON (completo)')}
        {dl(files.csv, 'CSV (rutas)')}
        {dl(files.deliveries_csv, 'CSV (entregas)')}
      </div>
    </div>
  );
}

export default function EvaluationDetailPage() {
  const params = useParams();
  const [evaluation, setEvaluation] = useState<Evaluation | null>(null);
  const [loading, setLoading] = useState(true);
  const [showParams, setShowParams] = useState(false);

  useEffect(() => {
    const id = Number(params.id);
    if (!id) return;
    evaluationsApi.get(id).then(setEvaluation).catch(console.error).finally(() => setLoading(false));
  }, [params.id]);

  if (loading) return <div className="p-4 text-gray-500">Cargando evaluación...</div>;
  if (!evaluation) return <div className="p-4 text-red-500">Evaluación no encontrada</div>;

  const ms = evaluation.metrics_summary;

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4">
        <a href="/evaluations" className="text-blue-600 hover:text-blue-800 text-sm">&larr; Volver</a>
        <h1 className="text-xl font-semibold">
          Evaluación #{evaluation.id}
          <span className="text-sm font-normal text-gray-500 ml-3">
            {new Date(evaluation.executed_at).toLocaleString('es-CL')}
          </span>
        </h1>
        <span className="text-xs text-gray-400 ml-auto">
          {evaluation.parameters?.dataset} | {evaluation.total_deliveries} entregas, {evaluation.total_routes} rutas
        </span>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <MetricCard label="Dist. Promedio General" value={n(ms.distancia_promedio_general_km)} unit="km" color="bg-blue-500" />
        <MetricCard label="Cobertura Territorial" value={n(ms.coverage_territorial_km)} unit="km" color="bg-green-500" />
        <MetricCard label="Desviación Estándar" value={n(ms.desviacion_estandar_distancias_km)} unit="km" color="bg-purple-500" />
        <MetricCard label="Dist. Mínima Inter-Cluster" value={n(ms.inter_cluster_min_distance_km)} unit="km" color="bg-teal-500" />
        <MetricCard label="Balance (CV)" value={n(ms.balance_general_cv, 4)} color="bg-amber-500" />
        <MetricCard label="Balance Index" value={n(ms.balance_index, 4)} color="bg-indigo-500" />
        <MetricCard label="Anomalías" value={String(ms.total_anomalias_detectadas)} color="bg-red-500" />
        <MetricCard label="Penalidad Operacional" value={n(ms.operational_penalty_total)} color="bg-orange-500" />
      </div>

      <div>
        <button
          onClick={() => setShowParams(!showParams)}
          className="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1"
        >
          {showParams ? '▼' : '▶'} Parámetros de la evaluación
        </button>
        {showParams && <div className="mt-2"><ParametersCard params={evaluation.parameters} /></div>}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div className="lg:col-span-2">
          <h2 className="text-lg font-semibold mb-3">Métricas por Ruta</h2>
          {evaluation.route_metrics && evaluation.route_metrics.length > 0 ? (
            <RouteMetricsTable routes={evaluation.route_metrics} />
          ) : (
            <div className="bg-white rounded border shadow-sm p-8 text-center text-gray-400">Sin datos de rutas</div>
          )}
        </div>
        <div>
          <Rankings evaluation={evaluation} />
        </div>
      </div>

      {evaluation.anomalies && evaluation.anomalies.length > 0 && (
        <AnomaliesTable anomalies={evaluation.anomalies} />
      )}

      <MapsSection evaluation={evaluation} />
      <DownloadsSection evaluation={evaluation} />
    </div>
  );
}
