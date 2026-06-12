'use client';

import { MapContainer, TileLayer, Marker, Popup } from 'react-leaflet';
import L from 'leaflet';
import { Package } from '@/lib/api';
import 'leaflet/dist/leaflet.css';

interface MapViewProps {
  packages: Package[];
  getRouteColor?: (pkg: Package) => string;
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

function createColoredIcon(color: string) {
  return L.divIcon({
    className: 'custom-marker',
    html: `<div style="background:${color};width:16px;height:16px;border-radius:50%;border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,0.3);"></div>`,
    iconSize: [16, 16],
    iconAnchor: [8, 8],
    popupAnchor: [0, -10],
  });
}

const COLORS = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'];

export default function MapView({ packages, getRouteColor }: MapViewProps) {
  const center: [number, number] = [-33.045, -71.55]; // Valparaíso center
  const defaultColor = '#3b82f6';

  return (
    <MapContainer center={center} zoom={12} className="w-full h-[600px] rounded border" scrollWheelZoom={true}>
      <TileLayer
        attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
      />
      {packages.map((pkg) => {
        const color = getRouteColor?.(pkg) || defaultColor;
        const icon = pkg.assigned ? createColoredIcon(color) : DefaultIcon;
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
