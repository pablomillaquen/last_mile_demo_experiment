'use client';

import { useState, FormEvent } from 'react';
import { Package } from '@/lib/api';

interface PackageFormProps {
  initialData?: Partial<Package>;
  onSubmit: (data: Record<string, unknown>) => Promise<void>;
  loading?: boolean;
}

export default function PackageForm({ initialData, onSubmit, loading }: PackageFormProps) {
  const [form, setForm] = useState({
    tracking_number: initialData?.tracking_number || '',
    recipient_name: initialData?.recipient_name || '',
    delivery_address: initialData?.delivery_address || '',
    district: initialData?.district || '',
    city: initialData?.city || '',
    latitude: initialData?.latitude?.toString() || '',
    longitude: initialData?.longitude?.toString() || '',
  });

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    await onSubmit({
      ...form,
      latitude: parseFloat(form.latitude),
      longitude: parseFloat(form.longitude),
    });
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4 max-w-lg">
      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">Tracking Number *</label>
        <input
          type="text" required
          value={form.tracking_number}
          onChange={(e) => setForm({ ...form, tracking_number: e.target.value })}
          className="w-full border rounded px-3 py-2 text-sm"
        />
      </div>
      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">Destinatario *</label>
        <input
          type="text" required
          value={form.recipient_name}
          onChange={(e) => setForm({ ...form, recipient_name: e.target.value })}
          className="w-full border rounded px-3 py-2 text-sm"
        />
      </div>
      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">Dirección *</label>
        <textarea
          required
          value={form.delivery_address}
          onChange={(e) => setForm({ ...form, delivery_address: e.target.value })}
          className="w-full border rounded px-3 py-2 text-sm"
          rows={2}
        />
      </div>
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Distrito</label>
          <input
            type="text"
            value={form.district}
            onChange={(e) => setForm({ ...form, district: e.target.value })}
            className="w-full border rounded px-3 py-2 text-sm"
          />
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
          <input
            type="text"
            value={form.city}
            onChange={(e) => setForm({ ...form, city: e.target.value })}
            className="w-full border rounded px-3 py-2 text-sm"
          />
        </div>
      </div>
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Latitud *</label>
          <input
            type="number" step="any" required
            value={form.latitude}
            onChange={(e) => setForm({ ...form, latitude: e.target.value })}
            className="w-full border rounded px-3 py-2 text-sm"
          />
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Longitud *</label>
          <input
            type="number" step="any" required
            value={form.longitude}
            onChange={(e) => setForm({ ...form, longitude: e.target.value })}
            className="w-full border rounded px-3 py-2 text-sm"
          />
        </div>
      </div>
      <div className="flex gap-3 pt-2">
        <button
          type="submit" disabled={loading}
          className="bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
        >
          {loading ? 'Guardando...' : initialData ? 'Actualizar' : 'Crear Paquete'}
        </button>
        <a
          href="/packages"
          className="border px-4 py-2 rounded text-sm font-medium text-gray-600 hover:bg-gray-50"
        >
          Cancelar
        </a>
      </div>
    </form>
  );
}
