'use client';

import { MapContainer, TileLayer, Marker, Tooltip, Polyline, useMap } from 'react-leaflet';
import L from 'leaflet';
import { RouteLeg } from '@/lib/api';
import { settingsApi, Settings } from '@/lib/api';
import { useEffect, useMemo, useRef, useState } from 'react';
import 'leaflet/dist/leaflet.css';

interface PolylineData {
  positions: [number, number][];
  color: string;
  name: string;
  routeId: number;
  opacity?: number;
}

interface SplitMapViewProps {
  polylines: PolylineData[];
  routeLegs?: RouteLeg[];
  routeColorById: Record<number, string>;
  routeNameById: Record<number, string>;
  visibleRoutes: Set<number>;
  isolatedRoute: number | null;
}

const DefaultIcon = L.icon({
  iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
  iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
});

L.Marker.prototype.options.icon = DefaultIcon;

const warehouseIcon = L.divIcon({
  className: 'warehouse-marker',
  html: `<div style="background:#2563eb;width:24px;height:24px;border-radius:4px;border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;color:white;font-size:14px;font-weight:bold;">B</div>`,
  iconSize: [24, 24],
  iconAnchor: [12, 12],
  popupAnchor: [0, -14],
});

function SyncController({
  siblingMapRef,
  syncingRef,
  storeRef,
}: {
  siblingMapRef: React.MutableRefObject<L.Map | null>;
  syncingRef: React.MutableRefObject<boolean>;
  storeRef: React.MutableRefObject<L.Map | null>;
}) {
  const map = useMap();

  useEffect(() => {
    storeRef.current = map;
  }, [map, storeRef]);

  useEffect(() => {
    const handler = () => {
      if (syncingRef.current) {
        syncingRef.current = false;
        return;
      }
      if (siblingMapRef.current) {
        syncingRef.current = true;
        siblingMapRef.current.setView(map.getCenter(), map.getZoom(), { animate: false });
      }
    };
    map.on('moveend', handler);
    return () => { map.off('moveend', handler); };
  }, [map, siblingMapRef, syncingRef]);

  return null;
}

function MapPanel({
  label, polylines, warehousePos, center, siblingMapRef, syncingRef, storeRef,
}: {
  label: string;
  polylines: PolylineData[];
  warehousePos: [number, number] | null;
  center: [number, number];
  siblingMapRef: React.MutableRefObject<L.Map | null>;
  syncingRef: React.MutableRefObject<boolean>;
  storeRef: React.MutableRefObject<L.Map | null>;
}) {
  return (
    <div className="w-full lg:w-1/2 relative">
      <div className="absolute top-2 left-2 z-[1000] bg-white/90 text-xs font-medium px-2 py-1 rounded shadow-sm">
        {label}
      </div>
      <MapContainer center={center} zoom={12} className="w-full h-[600px]" scrollWheelZoom={true}>
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        {polylines.filter(pl => pl.positions.length > 1).map((pl, i) => (
          <Polyline
            key={i}
            positions={pl.positions}
            pathOptions={{ color: pl.color, weight: 2.5, opacity: pl.opacity ?? 0.85 }}
          />
        ))}
        {warehousePos && (
          <Marker position={warehousePos} icon={warehouseIcon}>
            <Tooltip permanent direction="top" offset={[0, -14]}>
              Bodega
            </Tooltip>
          </Marker>
        )}
        <SyncController siblingMapRef={siblingMapRef} syncingRef={syncingRef} storeRef={storeRef} />
      </MapContainer>
    </div>
  );
}

export default function SplitMapView({
  polylines, routeLegs, routeColorById, routeNameById,
  visibleRoutes, isolatedRoute,
}: SplitMapViewProps) {
  const center: [number, number] = [-33.045, -71.55];
  const defaultColor = '#6b7280';
  const leftMapRef = useRef<L.Map | null>(null);
  const rightMapRef = useRef<L.Map | null>(null);
  const syncingRef = useRef(false);
  const [settings, setSettings] = useState<Settings | null>(null);

  useEffect(() => {
    settingsApi.get().then(setSettings).catch(() => {});
  }, []);

  const warehousePos: [number, number] | null = settings
    ? [parseFloat(settings.warehouse_lat), parseFloat(settings.warehouse_lng)]
    : null;

  const vialAvailable = routeLegs !== undefined && routeLegs.some(leg => leg.mode === 'vial');

  const vialPolylines = useMemo(() => {
    if (!routeLegs || routeLegs.length === 0) return [];
    const grouped = new Map<number, RouteLeg[]>();
    routeLegs.forEach(leg => {
      const existing = grouped.get(leg.route_id) || [];
      existing.push(leg);
      grouped.set(leg.route_id, existing);
    });
    const result: PolylineData[] = [];
    grouped.forEach((legs, routeId) => {
      const color = routeColorById?.[routeId] || defaultColor;
      const name = routeNameById?.[routeId] || `Route ${routeId}`;
      const positions: [number, number][] = [];
      legs.forEach((leg, i) => {
        if (leg.geometry) {
          if (i === 0) positions.push(...leg.geometry);
          else positions.push(...leg.geometry.slice(1));
        }
      });
      result.push({ positions, color, name, routeId });
    });
    return result;
  }, [routeLegs, routeColorById, routeNameById]);

  const leftPolylines = useMemo(() => {
    return (polylines || [])
      .filter(pl => visibleRoutes.has(pl.routeId))
      .map(pl => ({
        ...pl,
        opacity: isolatedRoute !== null
          ? (pl.routeId === isolatedRoute ? 1.0 : 0.2)
          : (pl.opacity ?? 0.7),
      }));
  }, [polylines, visibleRoutes, isolatedRoute]);

  const rightPolylines = useMemo(() => {
    if (!vialAvailable) return [];
    return (vialPolylines || [])
      .filter(pl => visibleRoutes.has(pl.routeId))
      .map(pl => ({
        ...pl,
        opacity: isolatedRoute !== null
          ? (pl.routeId === isolatedRoute ? 1.0 : 0.2)
          : (pl.opacity ?? 0.85),
      }));
  }, [vialPolylines, visibleRoutes, isolatedRoute, vialAvailable]);

  if (!vialAvailable) {
    return (
      <div className="bg-white rounded border shadow-sm">
        <MapPanel
          label="Geodésico"
          polylines={leftPolylines}
          warehousePos={warehousePos}
          center={center}
          siblingMapRef={rightMapRef}
          syncingRef={syncingRef}
          storeRef={leftMapRef}
        />
        <p className="text-xs text-amber-600 p-2">
          No hay geometría vial disponible para esta evaluación.
        </p>
      </div>
    );
  }

  return (
    <div className="bg-white rounded border shadow-sm">
      <div className="flex flex-col lg:flex-row gap-0">
        <MapPanel
          label="Geodésico"
          polylines={leftPolylines}
          warehousePos={warehousePos}
          center={center}
          siblingMapRef={rightMapRef}
          syncingRef={syncingRef}
          storeRef={leftMapRef}
        />
        <MapPanel
          label="Vial"
          polylines={rightPolylines}
          warehousePos={warehousePos}
          center={center}
          siblingMapRef={leftMapRef}
          syncingRef={syncingRef}
          storeRef={rightMapRef}
        />
      </div>
    </div>
  );
}
