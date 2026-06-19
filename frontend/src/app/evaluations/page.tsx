'use client';

import { useEffect, useState } from 'react';
import { evaluationsApi, Evaluation, MetricsSummary } from '@/lib/api';

function SummaryBadge({ label, value }: { label: string; value: string | number }) {
  return (
    <div className="bg-white rounded border shadow-sm p-3">
      <div className="text-xs text-gray-500 uppercase tracking-wide">{label}</div>
      <div className="text-lg font-semibold">{typeof value === 'number' ? value.toLocaleString('es-CL', { maximumFractionDigits: 2 }) : value}</div>
    </div>
  );
}

function EvaluationRow({ evaluation }: { evaluation: Evaluation }) {
  const ms = evaluation.metrics_summary;
  return (
    <a
      href={`/evaluations/${evaluation.id}`}
      className="block bg-white rounded border shadow-sm p-4 hover:shadow-md transition-shadow"
    >
      <div className="flex items-center justify-between mb-3">
        <div>
          <span className="text-sm text-gray-500">#{evaluation.id}</span>
          <span className="text-sm text-gray-500 ml-3">
            {new Date(evaluation.executed_at).toLocaleString('es-CL')}
          </span>
        </div>
        <div className="text-xs text-gray-400">
          {evaluation.parameters?.algorithm || 'N/A'} v{evaluation.parameters?.algorithm_version || '-'}
        </div>
      </div>

      <div className="flex items-center gap-4 mb-3 text-sm">
        <span className="flex items-center gap-1">
          <span className="w-2 h-2 rounded-full bg-blue-500"></span>
          {evaluation.total_deliveries} entregas
        </span>
        <span className="flex items-center gap-1">
          <span className="w-2 h-2 rounded-full bg-green-500"></span>
          {evaluation.total_routes} rutas
        </span>
      </div>

      <div className="grid grid-cols-2 sm:grid-cols-4 gap-2">
        <SummaryBadge label="Dist. Promedio" value={`${ms.distancia_promedio_general_km?.toFixed(1) || '?'} km`} />
        <SummaryBadge label="Cobertura" value={`${ms.coverage_territorial_km?.toFixed(1) || '?'} km`} />
        <SummaryBadge label="Balance" value={ms.balance_index?.toFixed(2) || '?'} />
        <SummaryBadge label="Penalidad" value={ms.operational_penalty_total?.toFixed(1) || '?'} />
      </div>
    </a>
  );
}

function NewEvaluationForm({ onCreated }: { onCreated: () => void }) {
  const [open, setOpen] = useState(false);
  const [running, setRunning] = useState(false);
  const [randomSeed, setRandomSeed] = useState('42');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setRunning(true);
    try {
      await evaluationsApi.run({ random_seed: parseInt(randomSeed) || 42 });
      setOpen(false);
      onCreated();
    } catch (err) {
      console.error(err);
      alert('Error al ejecutar evaluación');
    } finally {
      setRunning(false);
    }
  };

  if (!open) {
    return (
      <button onClick={() => setOpen(true)} className="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
        Nueva Evaluación
      </button>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="bg-white rounded border shadow-sm p-4 flex items-center gap-4">
      <label className="text-sm text-gray-600">
        Random Seed:
        <input type="number" value={randomSeed} onChange={e => setRandomSeed(e.target.value)} className="ml-2 border rounded px-2 py-1 w-20 text-sm" />
      </label>
      <button type="submit" disabled={running} className="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 disabled:opacity-50">
        {running ? 'Evaluando...' : 'Ejecutar'}
      </button>
      <button type="button" onClick={() => setOpen(false)} className="text-sm text-gray-500 hover:text-gray-700">Cancelar</button>
    </form>
  );
}

export default function EvaluationsPage() {
  const [evaluations, setEvaluations] = useState<Evaluation[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchEvaluations = async () => {
    try {
      const res = await evaluationsApi.list();
      setEvaluations(res.data);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchEvaluations(); }, []);

  if (loading) return <div className="p-4 text-gray-500">Cargando evaluaciones...</div>;

  return (
    <div>
      <div className="flex items-center justify-between mb-4">
        <h1 className="text-xl font-semibold">Evaluaciones</h1>
        <NewEvaluationForm onCreated={fetchEvaluations} />
      </div>
      {evaluations.length === 0 ? (
        <div className="bg-white rounded border shadow-sm p-8 text-center text-gray-400">
          No hay evaluaciones aún. Ejecuta una para comenzar.
        </div>
      ) : (
        <div className="space-y-3">
          {evaluations.map(e => <EvaluationRow key={e.id} evaluation={e} />)}
        </div>
      )}
    </div>
  );
}
