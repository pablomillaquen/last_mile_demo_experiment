'use client';

import { Experiment } from '@/lib/api';

interface Props {
  experiment: Experiment;
  onClick?: () => void;
}

export default function ExperimentCard({ experiment, onClick }: Props) {
  const date = experiment.created_at
    ? new Date(experiment.created_at).toLocaleDateString('es-CL')
    : '—';

  return (
    <div
      onClick={onClick}
      className="bg-white rounded border shadow-sm p-4 cursor-pointer hover:shadow-md transition-shadow"
    >
      <div className="flex items-start justify-between mb-2">
        <div>
          <span className="text-xs font-mono text-gray-400">{experiment.identifier}</span>
          <h3 className="font-semibold text-gray-900">{experiment.name}</h3>
        </div>
        <span className="text-xs text-gray-400">{date}</span>
      </div>
      <p className="text-sm text-gray-600 line-clamp-2 mb-3">{experiment.objective}</p>
      <div className="flex items-center gap-4 text-xs text-gray-500">
        <span>{experiment.evaluations_count} evaluación(es)</span>
        {experiment.author && <span>Por: {experiment.author}</span>}
      </div>
    </div>
  );
}
