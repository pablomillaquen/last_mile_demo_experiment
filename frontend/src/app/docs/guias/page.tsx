'use client';

import { useEffect, useState } from 'react';
import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';

const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

export default function GuiaPage() {
  const [content, setContent] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    fetch(`${API_BASE}/docs/guia-de-metricas`)
      .then((r) => {
        if (!r.ok) throw new Error('No se pudo cargar la guía');
        return r.text();
      })
      .then(setContent)
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="p-4 text-gray-500">Cargando guía de métricas...</div>;
  if (error) return <div className="p-4 text-red-500">Error: {error}</div>;

  return (
    <div className="space-y-6">
      <h1 className="text-xl font-semibold">Guía de Interpretación de Métricas</h1>
      <div className="bg-white rounded border shadow-sm p-6 prose prose-sm max-w-none">
        <ReactMarkdown remarkPlugins={[remarkGfm]}>{content || ''}</ReactMarkdown>
      </div>
    </div>
  );
}
