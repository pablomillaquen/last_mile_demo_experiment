'use client';

import { useRouter } from 'next/navigation';
import { useState } from 'react';
import { packagesApi } from '@/lib/api';
import PackageForm from '@/components/PackageForm';

export default function CreatePackagePage() {
  const router = useRouter();
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (data: Record<string, unknown>) => {
    setLoading(true);
    try {
      await packagesApi.create(data as Parameters<typeof packagesApi.create>[0]);
      router.push('/packages');
    } catch (err) {
      console.error('Error creating package', err);
      alert('Error al crear paquete');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <h1 className="text-xl font-semibold mb-6">Nuevo Paquete</h1>
      <div className="bg-white rounded border shadow-sm p-6">
        <PackageForm onSubmit={handleSubmit} loading={loading} />
      </div>
    </div>
  );
}
