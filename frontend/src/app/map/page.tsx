'use client';

import { useEffect, useState } from 'react';
import dynamic from 'next/dynamic';
import { packagesApi, routesApi, settingsApi, Package, Route, Settings } from '@/lib/api';

const MapView = dynamic(() => import('@/components/MapView'), { ssr: false });

const COLORS = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'];

interface PolylineData {
  positions: [number, number][];
  color: string;
  name: string;
}

export default function MapPage() {
  const [packages, setPackages] = useState<Package[]>([]);
  const [routes, setRoutes] = useState<Route[]>([]);
  const [settings, setSettings] = useState<Settings | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetch = async () => {
      try {
        const [pkgRes, routeRes, settingsData] = await Promise.all([
          packagesApi.list({ per_page: 200 }),
          routesApi.list(),
          settingsApi.get(),
        ]);
        setPackages(pkgRes.data);
        setRoutes(routeRes.data);
        setSettings(settingsData);
      } catch (err) {
        console.error(err);
      } finally {
        setLoading(false);
      }
    };
    fetch();
  }, []);

  const packageToRoute = new Map<number, number>();
  const packageToSequence = new Map<number, number>();

  routes.forEach((route, routeIdx) => {
    (route.route_packages || []).forEach((rp) => {
      if (rp.package) {
        packageToRoute.set(rp.package.id ?? rp.package_id, routeIdx);
      }
      if (rp.sequence !== null) {
        packageToSequence.set(rp.package_id, rp.sequence);
      }
    });
  });

  const getRouteColor = (pkg: Package): string => {
    const idx = packageToRoute.get(pkg.id);
    return idx !== undefined ? COLORS[idx % COLORS.length] : '#6b7280';
  };

  const getSequence = (pkg: Package): number | null => {
    return packageToSequence.get(pkg.id) ?? null;
  };

  const warehousePos: [number, number] | null = settings
    ? [parseFloat(settings.warehouse_lat), parseFloat(settings.warehouse_lng)]
    : null;

  const polylines: PolylineData[] = routes.map((route, idx) => {
    const ordered = (route.route_packages || [])
      .filter((rp) => rp.package)
      .sort((a, b) => (a.sequence ?? 0) - (b.sequence ?? 0));

    const positions: [number, number][] = [];

    if (warehousePos) {
      positions.push(warehousePos);
    }

    ordered.forEach((rp) => {
      if (rp.package) {
        positions.push([rp.package.latitude, rp.package.longitude]);
      }
    });

    if (warehousePos && positions.length > 1) {
      positions.push(warehousePos);
    }

    return {
      positions,
      color: COLORS[idx % COLORS.length],
      name: route.name,
    };
  });

  if (loading) return <div className="p-4 text-gray-500">Cargando mapa...</div>;

  return (
    <div>
      <h1 className="text-xl font-semibold mb-4">Mapa de Paquetes</h1>
      <div className="bg-white rounded border shadow-sm">
        <MapView
          packages={packages}
          getRouteColor={getRouteColor}
          getSequence={getSequence}
          polylines={polylines}
        />
      </div>
      <div className="flex gap-4 mt-3 text-sm text-gray-500 flex-wrap">
        <span className="flex items-center gap-1"><span className="w-3 h-3 rounded-full" style={{background:'#6b7280'}}></span> Sin ruta</span>
        {routes.map((r, i) => (
          <span key={r.id} className="flex items-center gap-1">
            <span className="w-3 h-3 rounded-full" style={{background: COLORS[i % COLORS.length]}}></span>
            {r.name}
          </span>
        ))}
      </div>
    </div>
  );
}
