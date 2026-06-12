import type { Metadata } from 'next';
import './globals.css';

export const metadata: Metadata = {
  title: 'Last Mile Demo',
  description: 'Simulación de operación logística — Asignación Manual',
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="es">
      <body className="min-h-screen bg-gray-50" suppressHydrationWarning>
        <nav className="bg-white border-b shadow-sm">
          <div className="max-w-7xl mx-auto px-4 h-14 flex items-center gap-6 text-sm">
            <a href="/packages" className="font-medium text-gray-700 hover:text-blue-600">Paquetes</a>
            <a href="/routes" className="font-medium text-gray-700 hover:text-blue-600">Rutas</a>
            <a href="/map" className="font-medium text-gray-700 hover:text-blue-600">Mapa</a>
            <a href="/dashboard" className="font-medium text-gray-700 hover:text-blue-600">Dashboard</a>
          </div>
        </nav>
        <main className="max-w-7xl mx-auto px-4 py-6">
          {children}
        </main>
      </body>
    </html>
  );
}
