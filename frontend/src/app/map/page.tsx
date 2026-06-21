'use client';

import { useEffect, useState, useMemo } from 'react';
import dynamic from 'next/dynamic';
import { packagesApi, routesApi, settingsApi, evaluationsApi, Package, Route, Settings, RouteLeg } from '@/lib/api';

const MapView = dynamic(() => import('@/components/MapView'), { ssr: false });
const RouteModeToggle = dynamic(() => import('@/components/RouteModeToggle'), { ssr: false });

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
  const [routeLegs, setRouteLegs] = useState<RouteLeg[] | undefined>(undefined);
  const [mode, setMode] = useState<'geodesic' | 'vial'>('geodesic');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetch = async () => {
      try {
        const [pkgRes, routeRes, settingsData, evalsRes] = await Promise.all([
          packagesApi.list({ per_page: 200 }),
          routesApi.list(),
          settingsApi.get(),
          evaluationsApi.list(),
        ]);
        setPackages(pkgRes.data);
        setRoutes(routeRes.data);
        setSettings(settingsData);

        const evaluations = evalsRes.data;
        if (evaluations && evaluations.length > 0) {
          const latestId = evaluations.reduce((a, b) => a.id > b.id ? a : b).id;
          const latest = await evaluationsApi.get(latestId);
          if (latest.route_legs) {
            setRouteLegs(latest.route_legs);
          }
        }
      } catch (err) {
        console.error(err);
      } finally {
        setLoading(false);
      }
    };
    fetch();
  }, []);

  const routeById = useMemo(() => {
    const map = new Map<number, Route>();
    routes.forEach(r => map.set(r.id, r));
    return map;
  }, [routes]);

  const routeColorById = useMemo(() => {
    const map: Record<number, string> = {};
    routes.forEach((r, i) => {
      map[r.id] = COLORS[i % COLORS.length];
    });
    return map;
  }, [routes]);

  const routeNameById = useMemo(() => {
    const map: Record<number, string> = {};
    routes.forEach(r => {
      map[r.id] = r.name;
    });
    return map;
  }, [routes]);

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

  const vialAvailable = routeLegs !== undefined && routeLegs.length > 0;

  if (loading) return <div className="p-4 text-gray-500">Cargando mapa...</div>;

  return (
    <div>
      <div className="flex items-center justify-between mb-4">
        <h1 className="text-xl font-semibold">Mapa de Paquetes</h1>
        <RouteModeToggle
          mode={mode}
          onModeChange={setMode}
          vialAvailable={vialAvailable}
        />
      </div>
      <div className="bg-white rounded border shadow-sm">
        <MapView
          packages={packages}
          getRouteColor={getRouteColor}
          getSequence={getSequence}
          polylines={polylines}
          routeLegs={routeLegs}
          mode={mode}
          routeColorById={routeColorById}
          routeNameById={routeNameById}
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
      {!vialAvailable && mode === 'vial' && (
        <p className="mt-2 text-xs text-amber-600">
          No hay geometría vial disponible. Las evaluaciones más recientes incluirán datos viales automáticamente.
        </p>
      )}
    </div>
  );
}
