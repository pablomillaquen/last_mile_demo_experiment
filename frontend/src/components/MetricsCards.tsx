'use client';

import { useEffect, useState } from 'react';

interface Metrics {
  total_packages: number;
  total_routes: number;
  packages_per_route: { average: number; min: number; max: number };
  unassigned_packages: number;
}

export default function MetricsCards() {
  const [metrics, setMetrics] = useState<Metrics | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const loadMetrics = async () => {
      try {
        const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api'}/metrics`);
        const data = await res.json();
        setMetrics(data);
      } catch (err) {
        console.error(err);
      } finally {
        setLoading(false);
      }
    };
    loadMetrics();
  }, []);

  if (loading) return <div className="p-4 text-gray-500">Cargando métricas...</div>;
  if (!metrics) return <div className="p-4 text-red-500">Error al cargar métricas</div>;

  const cards = [
    { label: 'Total Paquetes', value: metrics.total_packages, color: 'bg-blue-500' },
    { label: 'Total Rutas', value: metrics.total_routes, color: 'bg-green-500' },
    { label: 'Paquetes por Ruta (prom)', value: metrics.packages_per_route.average, color: 'bg-purple-500' },
    { label: 'Paquetes Sin Asignar', value: metrics.unassigned_packages, color: 'bg-amber-500' },
  ];

  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      {cards.map((card) => (
        <div key={card.label} className="bg-white rounded border shadow-sm p-4">
          <div className="flex items-center gap-3">
            <div className={`w-10 h-10 rounded ${card.color} flex items-center justify-center text-white font-bold text-sm`}>
              {card.value}
            </div>
            <div>
              <div className="text-xs text-gray-500 uppercase tracking-wide">{card.label}</div>
              <div className="text-xl font-semibold">{card.value}</div>
            </div>
          </div>
        </div>
      ))}
    </div>
  );
}
