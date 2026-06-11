'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import { routesApi, Route as RouteType, packagesApi, Package } from '@/lib/api';

export default function RouteDetailPage() {
  const { id } = useParams<{ id: string }>();
  const [route, setRoute] = useState<RouteType | null>(null);
  const [loading, setLoading] = useState(true);
  const [packages, setPackages] = useState<Package[]>([]);

  const fetchRoute = async () => {
    setLoading(true);
    try {
      const [routeData, pkgData] = await Promise.all([
        routesApi.get(Number(id)),
        packagesApi.list({ per_page: 200 }),
      ]);
      setRoute(routeData);
      setPackages(pkgData.data);
    } catch (err) {
      console.error('Error fetching route', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchRoute(); }, [id]);

  if (loading) return <div className="p-4 text-gray-500">Cargando ruta...</div>;
  if (!route) return <div className="p-4 text-red-500">Ruta no encontrada</div>;

  const assignedIds = new Set((route.route_packages || []).map((rp) => rp.package_id));
  const unassigned = packages.filter((p) => !p.assigned);
  const assigned = (route.route_packages || []).map((rp) => rp.package).filter(Boolean) as Package[];

  return (
    <div>
      <a href="/routes" className="text-sm text-blue-600 hover:underline mb-4 inline-block">&larr; Volver a rutas</a>
      <div className="bg-white rounded border shadow-sm p-6 mb-6">
        <h1 className="text-xl font-semibold mb-2">{route.name}</h1>
        <div className="text-sm text-gray-600 space-y-1">
          <p>Fecha: {route.route_date?.split('T')[0].split('-').reverse().join('/')}</p>
          <p>Paquetes asignados: {route.route_packages_count ?? 0}</p>
          {route.notes && <p>Notas: {route.notes}</p>}
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white rounded border shadow-sm p-4">
          <h2 className="text-lg font-semibold mb-3">Paquetes Asignados</h2>
          {assigned.length === 0 ? (
            <p className="text-sm text-gray-400">No hay paquetes asignados</p>
          ) : (
            <ul className="space-y-2">
              {assigned.map((pkg) => (
                <li key={pkg.id} className="flex items-center justify-between text-sm border-b pb-2">
                  <span>{pkg.tracking_number} - {pkg.recipient_name}</span>
                  <button
                    onClick={async () => {
                      try {
                        await routesApi.unassign(route.id, pkg.id);
                        fetchRoute();
                      } catch (err) {
                        alert('Error al desasignar');
                      }
                    }}
                    className="text-red-600 hover:underline text-xs"
                  >
                    Desasignar
                  </button>
                </li>
              ))}
            </ul>
          )}
        </div>

        <div className="bg-white rounded border shadow-sm p-4">
          <h2 className="text-lg font-semibold mb-3">Paquetes Disponibles</h2>
          {unassigned.length === 0 ? (
            <p className="text-sm text-gray-400">No hay paquetes disponibles</p>
          ) : (
            <ul className="space-y-2 max-h-96 overflow-y-auto">
              {unassigned.map((pkg) => (
                <li key={pkg.id} className="flex items-center justify-between text-sm border-b pb-2">
                  <span>{pkg.tracking_number} - {pkg.recipient_name}</span>
                  <button
                    onClick={async () => {
                      try {
                        await routesApi.assign(route.id, pkg.id);
                        fetchRoute();
                      } catch (err) {
                        alert('Error al asignar');
                      }
                    }}
                    className="text-blue-600 hover:underline text-xs"
                  >
                    Asignar
                  </button>
                </li>
              ))}
            </ul>
          )}
        </div>
      </div>
    </div>
  );
}
