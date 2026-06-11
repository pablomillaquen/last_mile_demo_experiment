'use client';

import { useEffect, useState } from 'react';
import dynamic from 'next/dynamic';
import { packagesApi, routesApi, Package } from '@/lib/api';

const MapView = dynamic(() => import('@/components/MapView'), { ssr: false });

const COLORS = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'];

export default function MapPage() {
  const [packages, setPackages] = useState<Package[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetch = async () => {
      try {
        const [pkgRes, routeRes] = await Promise.all([
          packagesApi.list({ per_page: 200 }),
          routesApi.list(),
        ]);
        setPackages(pkgRes.data);
      } catch (err) {
        console.error(err);
      } finally {
        setLoading(false);
      }
    };
    fetch();
  }, []);

  const getRouteColor = (pkg: Package): string => {
    return COLORS[pkg.id % COLORS.length];
  };

  if (loading) return <div className="p-4 text-gray-500">Cargando mapa...</div>;

  return (
    <div>
      <h1 className="text-xl font-semibold mb-4">Mapa de Paquetes</h1>
      <div className="bg-white rounded border shadow-sm">
        <MapView packages={packages} getRouteColor={getRouteColor} />
      </div>
      <div className="flex gap-4 mt-3 text-sm text-gray-500">
        <span className="flex items-center gap-1"><span className="w-3 h-3 rounded-full bg-blue-600"></span> Pendiente</span>
        {COLORS.map((c, i) => (
          <span key={i} className="flex items-center gap-1"><span className="w-3 h-3 rounded-full" style={{background:c}}></span> Ruta {i + 1}</span>
        ))}
      </div>
    </div>
  );
}
