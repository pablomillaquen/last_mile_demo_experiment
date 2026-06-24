'use client';

import { MapContainer, TileLayer, Marker, Popup, Tooltip, Polyline } from 'react-leaflet';
import L from 'leaflet';
import { Package, RouteLeg } from '@/lib/api';
import { settingsApi, Settings } from '@/lib/api';
import { useEffect, useState, useMemo } from 'react';
import 'leaflet/dist/leaflet.css';

interface PolylineData {
  positions: [number, number][];
  color: string;
  name: string;
  routeId: number;
  opacity?: number;
}

interface MapViewProps {
  packages: Package[];
  getRouteColor?: (pkg: Package) => string;
  getSequence?: (pkg: Package) => number | null;
  polylines?: PolylineData[];
  routeLegs?: RouteLeg[];
  mode?: 'geodesic' | 'vial';
  routeColorById?: Record<number, string>;
  routeNameById?: Record<number, string>;
  visibleRoutes?: Set<number>;
  isolatedRoute?: number | null;
}

// Fix default marker icons
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

function createColoredIcon(color: string, sequence?: number) {
  const label = sequence !== undefined ? `<span style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:white;font-size:10px;font-weight:bold;text-shadow:0 0 2px rgba(0,0,0,0.8);">${sequence}</span>` : '';
  return L.divIcon({
    className: 'custom-marker',
    html: `<div style="background:${color};width:20px;height:20px;border-radius:50%;border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,0.3);position:relative;">${label}</div>`,
    iconSize: [20, 20],
    iconAnchor: [10, 10],
    popupAnchor: [0, -12],
  });
}

const COLORS = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'];

export default function MapView({
  packages, getRouteColor, getSequence, polylines,
  routeLegs, mode = 'geodesic', routeColorById, routeNameById,
  visibleRoutes, isolatedRoute,
}: MapViewProps) {
  const center: [number, number] = [-33.045, -71.55];
  const defaultColor = '#3b82f6';
  const [settings, setSettings] = useState<Settings | null>(null);

  useEffect(() => {
    settingsApi.get().then(setSettings).catch(() => {});
  }, []);

  const warehousePos: [number, number] | null = settings
    ? [parseFloat(settings.warehouse_lat), parseFloat(settings.warehouse_lng)]
    : null;

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
          if (i === 0) {
            positions.push(...leg.geometry);
          } else {
            positions.push(...leg.geometry.slice(1));
          }
        }
      });
      result.push({ positions, color, name, routeId });
    });
    return result;
  }, [routeLegs, routeColorById, routeNameById]);

  const activePolylines = mode === 'vial' && vialPolylines.length > 0 ? vialPolylines : polylines;

  const displayPolylines = useMemo(() => {
    if (!activePolylines) return [];
    return activePolylines
      .filter(pl => !visibleRoutes || visibleRoutes.has(pl.routeId))
      .map(pl => ({
        ...pl,
        opacity: isolatedRoute != null
          ? (pl.routeId === isolatedRoute ? 1.0 : 0.2)
          : (pl.opacity ?? (mode === 'vial' ? 0.85 : 0.7)),
      }));
  }, [activePolylines, visibleRoutes, isolatedRoute, mode]);

  return (
    <MapContainer center={center} zoom={12} className="w-full h-[600px] rounded border" scrollWheelZoom={true}>
      <TileLayer
        attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
      />
      {displayPolylines.filter(pl => pl.positions.length > 1).map((pl, i) => (
        <Polyline
          key={i}
          positions={pl.positions}
          pathOptions={{ color: pl.color, weight: mode === 'vial' ? 2.5 : 3, opacity: pl.opacity }}
        >
          <Tooltip sticky direction="top">{pl.name}</Tooltip>
        </Polyline>
      ))}
      {warehousePos && (
        <Marker position={warehousePos} icon={warehouseIcon}>
          <Tooltip permanent direction="top" offset={[0, -14]}>
            Bodega
          </Tooltip>
          <Popup>
            <div className="text-sm">
              <strong>Bodega Central</strong><br />
              Lat: {settings?.warehouse_lat}<br />
              Lng: {settings?.warehouse_lng}
            </div>
          </Popup>
        </Marker>
      )}
      {packages.map((pkg) => {
        const color = getRouteColor?.(pkg) || defaultColor;
        const seq = getSequence?.(pkg);
        const icon = pkg.assigned ? createColoredIcon(color, seq ?? undefined) : DefaultIcon;
        return (
          <Marker
            key={pkg.id}
            position={[pkg.latitude, pkg.longitude]}
            icon={icon}
          >
            <Popup>
              <div className="text-sm">
                <strong>{pkg.tracking_number}</strong><br />
                {pkg.recipient_name}<br />
                {pkg.delivery_address}<br />
                <span className={pkg.assigned ? 'text-blue-600' : 'text-gray-500'}>
                  {pkg.assigned ? 'Asignado' : 'Pendiente'}
                </span>
              </div>
            </Popup>
          </Marker>
        );
      })}
    </MapContainer>
  );
}
