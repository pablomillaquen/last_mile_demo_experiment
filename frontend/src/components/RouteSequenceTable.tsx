'use client';

import { RoutePackage } from '@/lib/api';

interface Props {
  routePackages: RoutePackage[];
  onUnassign: (packageId: number) => void;
}

export default function RouteSequenceTable({ routePackages, onUnassign }: Props) {
  return (
    <div className="overflow-x-auto">
      <table className="w-full text-sm">
        <thead>
          <tr className="border-b text-left text-gray-500">
            <th className="pb-2 pr-2">Secuencia</th>
            <th className="pb-2 pr-2">Tracking</th>
            <th className="pb-2 pr-2">Destinatario</th>
            <th className="pb-2 pr-2">Dirección</th>
            <th className="pb-2"></th>
          </tr>
        </thead>
        <tbody>
          {routePackages.map((rp) => (
            <tr key={rp.id} className="border-b last:border-0">
              <td className="py-2 pr-2 font-semibold text-gray-700">{rp.sequence}</td>
              <td className="py-2 pr-2">{rp.package?.tracking_number}</td>
              <td className="py-2 pr-2">{rp.package?.recipient_name}</td>
              <td className="py-2 pr-2 text-gray-500">{rp.package?.delivery_address}</td>
              <td className="py-2">
                <button
                  onClick={() => onUnassign(rp.package_id)}
                  className="text-red-600 hover:underline text-xs"
                >
                  Desasignar
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
