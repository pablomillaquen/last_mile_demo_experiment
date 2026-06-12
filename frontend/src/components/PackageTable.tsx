'use client';

import { Package } from '@/lib/api';

interface PackageTableProps {
  packages: Package[];
  loading: boolean;
  onEdit?: (pkg: Package) => void;
  onDelete?: (id: number) => void;
}

export default function PackageTable({ packages, loading, onEdit, onDelete }: PackageTableProps) {
  if (loading) return <div className="p-4 text-gray-500">Cargando paquetes...</div>;

  return (
    <div className="overflow-x-auto">
      <table className="w-full border-collapse">
        <thead>
          <tr className="border-b bg-gray-50">
            <th className="text-left p-3 text-sm font-medium text-gray-600">Tracking</th>
            <th className="text-left p-3 text-sm font-medium text-gray-600">Destinatario</th>
            <th className="text-left p-3 text-sm font-medium text-gray-600">Dirección</th>
            <th className="text-left p-3 text-sm font-medium text-gray-600">Distrito</th>
            <th className="text-left p-3 text-sm font-medium text-gray-600">Estado</th>
            <th className="text-right p-3 text-sm font-medium text-gray-600">Acciones</th>
          </tr>
        </thead>
        <tbody>
          {packages.map((pkg) => (
            <tr key={pkg.id} className="border-b hover:bg-gray-50 transition-colors">
              <td className="p-3 text-sm">{pkg.tracking_number}</td>
              <td className="p-3 text-sm font-medium">{pkg.recipient_name}</td>
              <td className="p-3 text-sm text-gray-600 max-w-xs truncate">{pkg.delivery_address}</td>
              <td className="p-3 text-sm text-gray-600">{pkg.district || '-'}</td>
              <td className="p-3 text-sm">
                <span className={`inline-block px-2 py-1 rounded text-xs font-medium ${
                  pkg.assigned ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'
                }`}>
                  {pkg.assigned ? 'Asignado' : 'Pendiente'}
                </span>
              </td>
              <td className="p-3 text-sm text-right">
                {onEdit && (
                  <button onClick={() => onEdit(pkg)} className="text-blue-600 hover:underline mr-3">Editar</button>
                )}
                {onDelete && (
                  <button onClick={() => onDelete(pkg.id)} className="text-red-600 hover:underline">Eliminar</button>
                )}
              </td>
            </tr>
          ))}
          {packages.length === 0 && (
            <tr>
              <td colSpan={6} className="p-6 text-center text-gray-400">No hay paquetes registrados</td>
            </tr>
          )}
        </tbody>
      </table>
    </div>
  );
}
