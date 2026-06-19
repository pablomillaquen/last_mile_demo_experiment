'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { experimentsApi, Experiment } from '@/lib/api';
import ExperimentCard from '@/components/ExperimentCard';

export default function ExperimentsPage() {
  const router = useRouter();
  const [experiments, setExperiments] = useState<Experiment[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    experimentsApi
      .list()
      .then((res) => setExperiments(res.data))
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="p-4 text-gray-500">Cargando experimentos...</div>;
  if (error) return <div className="p-4 text-red-500">Error: {error}</div>;

  return (
    <div className="space-y-6">
      <h1 className="text-xl font-semibold">Experimentos</h1>
      {experiments.length === 0 ? (
        <div className="bg-white rounded border shadow-sm p-8 text-center text-gray-400">
          No hay experimentos registrados. Crea un experimento manualmente en el directorio <code>experiments/</code> y ejecuta <code>php artisan experiments:sync</code>.
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {experiments.map((exp) => (
            <ExperimentCard
              key={exp.id}
              experiment={exp}
              onClick={() => router.push(`/experiments/${exp.id}`)}
            />
          ))}
        </div>
      )}
    </div>
  );
}
