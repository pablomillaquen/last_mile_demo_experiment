'use client';

import { useState } from 'react';

interface RoutePanelProps {
  routes: { id: number; name: string }[];
  routeColorById: Record<number, string>;
  visibleRoutes: Set<number>;
  isolatedRoute: number | null;
  onToggleRoute: (routeId: number) => void;
  onIsolateRoute: (routeId: number | null) => void;
  onSelectAll: () => void;
  onDeselectAll: () => void;
}

export default function RoutePanel({
  routes, routeColorById, visibleRoutes, isolatedRoute,
  onToggleRoute, onIsolateRoute, onSelectAll, onDeselectAll,
}: RoutePanelProps) {
  const [collapsed, setCollapsed] = useState(false);
  const allVisible = routes.length > 0 && routes.every(r => visibleRoutes.has(r.id));

  if (routes.length === 0) return null;

  return (
    <div className="bg-white rounded border shadow-sm">
      <button
        onClick={() => setCollapsed(!collapsed)}
        className="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
      >
        <span>Rutas ({routes.length})</span>
        <span className="text-gray-400">{collapsed ? '◀' : '▼'}</span>
      </button>
      {!collapsed && (
        <div className="px-3 pb-2 space-y-1 max-h-80 overflow-y-auto">
          <div className="flex gap-2 pb-1.5 border-b border-gray-100">
            <button
              onClick={onSelectAll}
              disabled={allVisible}
              className="text-xs text-blue-600 hover:text-blue-800 disabled:text-gray-300 disabled:cursor-not-allowed"
            >
              Seleccionar todas
            </button>
            <button
              onClick={onDeselectAll}
              disabled={visibleRoutes.size === 0}
              className="text-xs text-blue-600 hover:text-blue-800 disabled:text-gray-300 disabled:cursor-not-allowed"
            >
              Deseleccionar todas
            </button>
          </div>
          {routes.map(r => {
            const isVisible = visibleRoutes.has(r.id);
            const isIsolated = isolatedRoute === r.id;
            const color = routeColorById[r.id] || '#6b7280';

            return (
              <div
                key={r.id}
                className={`flex items-center gap-2 py-1 px-1.5 rounded cursor-pointer transition-colors ${
                  isIsolated ? 'bg-blue-50 border-l-4' : 'hover:bg-gray-50 border-l-4 border-l-transparent'
                }`}
                style={isIsolated ? { borderLeftColor: color } : undefined}
                onClick={() => onIsolateRoute(isIsolated ? null : r.id)}
                title={isIsolated ? 'Salir de aislamiento' : `Aislar ${r.name}`}
              >
                <input
                  type="checkbox"
                  checked={isVisible}
                  onChange={e => { e.stopPropagation(); onToggleRoute(r.id); }}
                  className="cursor-pointer accent-blue-600 shrink-0"
                />
                <span
                  className="w-2.5 h-2.5 rounded-full shrink-0"
                  style={{ backgroundColor: color }}
                />
                <span
                  className={`text-sm truncate ${!isVisible ? 'text-gray-400' : isIsolated ? 'font-semibold text-gray-900' : 'text-gray-700'}`}
                  style={{ opacity: !isVisible ? 0.5 : 1 }}
                >
                  {r.name}
                </span>
                {isIsolated && (
                  <span className="ml-auto text-[10px] text-blue-600 font-medium shrink-0">Aislada</span>
                )}
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}
