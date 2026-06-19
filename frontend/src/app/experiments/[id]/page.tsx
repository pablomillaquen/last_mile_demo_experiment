'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import { experimentsApi, Experiment, Evaluation } from '@/lib/api';
import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';

function n(v: number | undefined | null, decimals = 2): string {
  if (v === undefined || v === null) return '-';
  return v.toLocaleString('es-CL', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
}

export default function ExperimentDetailPage() {
  const params = useParams();
  const [experiment, setExperiment] = useState<Experiment | null>(null);
  const [reportMd, setReportMd] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const id = Number(params.id);
    if (!id) return;

    Promise.all([
      experimentsApi.get(id),
      fetch(experimentsApi.reportUrl(id)).then((r) => (r.ok ? r.text() : null)),
    ])
      .then(([exp, md]) => {
        setExperiment(exp);
        setReportMd(md);
      })
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false));
  }, [params.id]);

  if (loading) return <div className="p-4 text-gray-500">Cargando experimento...</div>;
  if (error) return <div className="p-4 text-red-500">Error: {error}</div>;
  if (!experiment) return <div className="p-4 text-red-500">Experimento no encontrado</div>;

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4">
        <a href="/experiments" className="text-blue-600 hover:text-blue-800 text-sm">&larr; Volver</a>
        <h1 className="text-xl font-semibold">{experiment.name}</h1>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div className="lg:col-span-2">
          <div className="bg-white rounded border shadow-sm p-4 space-y-2 text-sm">
            <div><span className="font-medium text-gray-700">Identificador:</span> <code>{experiment.identifier}</code></div>
            <div><span className="font-medium text-gray-700">Objetivo:</span> {experiment.objective}</div>
            {experiment.hypothesis && <div><span className="font-medium text-gray-700">Hipótesis:</span> {experiment.hypothesis}</div>}
            {experiment.description && <div><span className="font-medium text-gray-700">Descripción:</span> {experiment.description}</div>}
            <div><span className="font-medium text-gray-700">Evaluaciones:</span> {experiment.evaluations_count}</div>
            {experiment.author && <div><span className="font-medium text-gray-700">Autor:</span> {experiment.author}</div>}
          </div>
        </div>
        <div className="space-y-2">
          <a
            href={experimentsApi.reportPdfUrl(experiment.id)}
            target="_blank"
            rel="noopener noreferrer"
            className="block w-full text-center bg-blue-600 text-white rounded py-2 text-sm font-medium hover:bg-blue-700"
          >
            Descargar PDF
          </a>
        </div>
      </div>

      {experiment.evaluations && experiment.evaluations.length > 0 && (
        <div>
          <h2 className="text-lg font-semibold mb-3">Evaluaciones Asociadas</h2>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            {experiment.evaluations.map((evalItem: Evaluation) => (
              <a
                key={evalItem.id}
                href={`/evaluations/${evalItem.id}`}
                className="bg-white rounded border shadow-sm p-3 hover:shadow-md transition-shadow block"
              >
                <div className="text-sm font-medium">Evaluación #{evalItem.id}</div>
                <div className="text-xs text-gray-500">
                  {new Date(evalItem.executed_at).toLocaleDateString('es-CL')}
                </div>
                <div className="text-xs text-gray-400 mt-1">
                  {evalItem.total_deliveries} entregas, {evalItem.total_routes} rutas
                </div>
              </a>
            ))}
          </div>
        </div>
      )}

      {reportMd && (
        <div className="bg-white rounded border shadow-sm p-6 prose prose-sm max-w-none">
          <ReactMarkdown remarkPlugins={[remarkGfm]}>{reportMd}</ReactMarkdown>
        </div>
      )}
    </div>
  );
}
