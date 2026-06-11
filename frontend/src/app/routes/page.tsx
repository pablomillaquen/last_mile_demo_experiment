'use client';

import { useEffect, useState } from 'react';
import { routesApi, Route as RouteType } from '@/lib/api';
import RouteTable from '@/components/RouteTable';

export default function RoutesPage() {
  const [routes, setRoutes] = useState<RouteType[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchRoutes = async () => {
    setLoading(true);
    try {
      const res = await routesApi.list();
      setRoutes(res.data);
    } catch (err) {
      console.error('Error fetching routes', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchRoutes(); }, []);

  const handleDelete = async (id: number) => {
    if (!confirm('¿Eliminar esta ruta?')) return;
    try {
      await routesApi.delete(id);
      fetchRoutes();
    } catch (err) {
      console.error('Error deleting route', err);
    }
  };

  return (
    <div>
      <div className="flex items-center justify-between mb-4">
        <h1 className="text-xl font-semibold">Rutas</h1>
        <a
          href="/routes/create"
          className="bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-700"
        >
          + Nueva Ruta
        </a>
      </div>
      <div className="bg-white rounded border shadow-sm">
        <RouteTable
          routes={routes}
          loading={loading}
          onDelete={handleDelete}
        />
      </div>
    </div>
  );
}
