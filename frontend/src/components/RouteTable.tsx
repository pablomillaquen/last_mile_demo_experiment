'use client';

import { Route } from '@/lib/api';

interface RouteTableProps {
  routes: Route[];
  loading: boolean;
  onDelete?: (id: number) => void;
}

export default function RouteTable({ routes, loading, onDelete }: RouteTableProps) {
  if (loading) return <div className="p-4 text-gray-500">Cargando rutas...</div>;

  return (
    <div className="overflow-x-auto">
      <table className="w-full border-collapse">
        <thead>
          <tr className="border-b bg-gray-50">
            <th className="text-left p-3 text-sm font-medium text-gray-600">Nombre</th>
            <th className="text-left p-3 text-sm font-medium text-gray-600">Fecha</th>
            <th className="text-left p-3 text-sm font-medium text-gray-600">Paquetes</th>
            <th className="text-left p-3 text-sm font-medium text-gray-600">Notas</th>
            <th className="text-right p-3 text-sm font-medium text-gray-600">Acciones</th>
          </tr>
        </thead>
        <tbody>
          {routes.map((route) => (
            <tr key={route.id} className="border-b hover:bg-gray-50 transition-colors">
              <td className="p-3 text-sm font-medium">
                <a href={`/routes/${route.id}`} className="text-blue-600 hover:underline">{route.name}</a>
              </td>
              <td className="p-3 text-sm text-gray-600">{route.route_date?.split('T')[0].split('-').reverse().join('/')}</td>
              <td className="p-3 text-sm">{route.route_packages_count ?? 0}</td>
              <td className="p-3 text-sm text-gray-600 max-w-xs truncate">{route.notes || '-'}</td>
              <td className="p-3 text-sm text-right">
                {onDelete && (
                  <button onClick={() => onDelete(route.id)} className="text-red-600 hover:underline">Eliminar</button>
                )}
              </td>
            </tr>
          ))}
          {routes.length === 0 && (
            <tr>
              <td colSpan={5} className="p-6 text-center text-gray-400">No hay rutas registradas</td>
            </tr>
          )}
        </tbody>
      </table>
    </div>
  );
}
