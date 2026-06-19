'use client';

import { useEffect, useState } from 'react';
import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';

const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

export default function PantallasPage() {
  const [evaluacionesMd, setEvaluacionesMd] = useState<string | null>(null);
  const [detalleMd, setDetalleMd] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    Promise.all([
      fetch(`${API_BASE}/docs/pantallas/evaluaciones`).then((r) => (r.ok ? r.text() : null)),
      fetch(`${API_BASE}/docs/pantallas/detalle-evaluacion`).then((r) => (r.ok ? r.text() : null)),
    ])
      .then(([evaluaciones, detalle]) => {
        setEvaluacionesMd(evaluaciones);
        setDetalleMd(detalle);
      })
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="p-4 text-gray-500">Cargando documentación...</div>;
  if (error) return <div className="p-4 text-red-500">Error: {error}</div>;

  return (
    <div className="space-y-6">
      <h1 className="text-xl font-semibold">Documentación de Pantallas</h1>
      {evaluacionesMd && (
        <div className="bg-white rounded border shadow-sm p-6 prose prose-sm max-w-none">
          <ReactMarkdown remarkPlugins={[remarkGfm]}>{evaluacionesMd}</ReactMarkdown>
        </div>
      )}
      {detalleMd && (
        <div className="bg-white rounded border shadow-sm p-6 prose prose-sm max-w-none">
          <ReactMarkdown remarkPlugins={[remarkGfm]}>{detalleMd}</ReactMarkdown>
        </div>
      )}
    </div>
  );
}
