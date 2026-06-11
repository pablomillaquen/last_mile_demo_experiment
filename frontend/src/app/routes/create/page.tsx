'use client';

import { useRouter } from 'next/navigation';
import { useState } from 'react';
import { routesApi } from '@/lib/api';
import RouteForm from '@/components/RouteForm';

export default function CreateRoutePage() {
  const router = useRouter();
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (data: Record<string, unknown>) => {
    setLoading(true);
    try {
      await routesApi.create(data as { name: string; route_date: string; notes?: string });
      router.push('/routes');
    } catch (err) {
      console.error('Error creating route', err);
      alert('Error al crear ruta');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <h1 className="text-xl font-semibold mb-6">Nueva Ruta</h1>
      <div className="bg-white rounded border shadow-sm p-6">
        <RouteForm onSubmit={handleSubmit} loading={loading} />
      </div>
    </div>
  );
}
