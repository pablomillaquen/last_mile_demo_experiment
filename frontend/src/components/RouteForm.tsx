'use client';

import { useState, FormEvent } from 'react';
import { Route } from '@/lib/api';

interface RouteFormProps {
  initialData?: Partial<Route>;
  onSubmit: (data: Record<string, unknown>) => Promise<void>;
  loading?: boolean;
}

export default function RouteForm({ initialData, onSubmit, loading }: RouteFormProps) {
  const [form, setForm] = useState({
    name: initialData?.name || '',
    route_date: initialData?.route_date || '',
    notes: initialData?.notes || '',
  });

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    await onSubmit({ ...form });
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4 max-w-lg">
      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
        <input
          type="text" required
          value={form.name}
          onChange={(e) => setForm({ ...form, name: e.target.value })}
          className="w-full border rounded px-3 py-2 text-sm"
        />
      </div>
      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">Fecha *</label>
        <input
          type="date" required
          value={form.route_date}
          onChange={(e) => setForm({ ...form, route_date: e.target.value })}
          className="w-full border rounded px-3 py-2 text-sm"
        />
      </div>
      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">Notas</label>
        <textarea
          value={form.notes}
          onChange={(e) => setForm({ ...form, notes: e.target.value })}
          className="w-full border rounded px-3 py-2 text-sm"
          rows={3}
        />
      </div>
      <div className="flex gap-3 pt-2">
        <button
          type="submit" disabled={loading}
          className="bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
        >
          {loading ? 'Guardando...' : initialData ? 'Actualizar' : 'Crear Ruta'}
        </button>
        <a
          href="/routes"
          className="border px-4 py-2 rounded text-sm font-medium text-gray-600 hover:bg-gray-50"
        >
          Cancelar
        </a>
      </div>
    </form>
  );
}
