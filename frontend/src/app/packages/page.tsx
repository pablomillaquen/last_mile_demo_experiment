'use client';

import { useEffect, useState } from 'react';
import { packagesApi, Package } from '@/lib/api';
import PackageTable from '@/components/PackageTable';

export default function PackagesPage() {
  const [packages, setPackages] = useState<Package[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchPackages = async () => {
    setLoading(true);
    try {
      const res = await packagesApi.list({ per_page: 50 });
      setPackages(res.data);
    } catch (err) {
      console.error('Error fetching packages', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchPackages(); }, []);

  const handleDelete = async (id: number) => {
    if (!confirm('¿Eliminar este paquete?')) return;
    try {
      await packagesApi.delete(id);
      fetchPackages();
    } catch (err) {
      console.error('Error deleting package', err);
    }
  };

  return (
    <div>
      <div className="flex items-center justify-between mb-4">
        <h1 className="text-xl font-semibold">Paquetes</h1>
        <a
          href="/packages/create"
          className="bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-700"
        >
          + Nuevo Paquete
        </a>
      </div>
      <div className="bg-white rounded border shadow-sm">
        <PackageTable
          packages={packages}
          loading={loading}
          onDelete={handleDelete}
        />
      </div>
    </div>
  );
}
