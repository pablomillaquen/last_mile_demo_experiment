'use client';

import SettingsForm from '@/components/SettingsForm';

export default function SettingsPage() {
  return (
    <div>
      <h1 className="text-xl font-semibold mb-4">Configuración</h1>
      <div className="bg-white rounded border shadow-sm p-6">
        <SettingsForm />
      </div>
    </div>
  );
}
