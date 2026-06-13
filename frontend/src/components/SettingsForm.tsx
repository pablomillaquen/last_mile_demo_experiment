'use client';

import { useEffect, useState } from 'react';
import { settingsApi, Settings } from '@/lib/api';

export default function SettingsForm() {
  const [form, setForm] = useState<Settings>({
    warehouse_lat: '',
    warehouse_lng: '',
    average_speed_kmh: '',
  });
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState('');

  useEffect(() => {
    const load = async () => {
      try {
        const data = await settingsApi.get();
        setForm(data);
      } catch {
        setMessage('Error al cargar configuración');
      } finally {
        setLoading(false);
      }
    };
    load();
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    setMessage('');
    try {
      const updated = await settingsApi.update(form);
      setForm(updated);
      setMessage('Configuración guardada correctamente');
    } catch {
      setMessage('Error al guardar configuración');
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <div className="p-4 text-gray-500">Cargando configuración...</div>;

  return (
    <form onSubmit={handleSubmit} className="space-y-4 max-w-md">
      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">
          Latitud Bodega
        </label>
        <input
          type="number"
          step="any"
          value={form.warehouse_lat}
          onChange={(e) => setForm({ ...form, warehouse_lat: e.target.value })}
          className="w-full border rounded px-3 py-2 text-sm"
          required
        />
      </div>
      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">
          Longitud Bodega
        </label>
        <input
          type="number"
          step="any"
          value={form.warehouse_lng}
          onChange={(e) => setForm({ ...form, warehouse_lng: e.target.value })}
          className="w-full border rounded px-3 py-2 text-sm"
          required
        />
      </div>
      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">
          Velocidad Promedio (km/h)
        </label>
        <input
          type="number"
          min="1"
          value={form.average_speed_kmh}
          onChange={(e) => setForm({ ...form, average_speed_kmh: e.target.value })}
          className="w-full border rounded px-3 py-2 text-sm"
          required
        />
      </div>
      <button
        type="submit"
        disabled={saving}
        className="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 disabled:opacity-50"
      >
        {saving ? 'Guardando...' : 'Guardar'}
      </button>
      {message && (
        <p className={`text-sm ${message.includes('Error') ? 'text-red-600' : 'text-green-600'}`}>
          {message}
        </p>
      )}
    </form>
  );
}
