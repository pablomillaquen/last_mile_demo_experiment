'use client';

export type RouteDisplayMode = 'geodesic' | 'vial';

interface RouteModeToggleProps {
  mode: RouteDisplayMode;
  onModeChange: (mode: RouteDisplayMode) => void;
  vialAvailable: boolean;
}

export default function RouteModeToggle({ mode, onModeChange, vialAvailable }: RouteModeToggleProps) {
  return (
    <div className="flex items-center gap-1 bg-gray-100 rounded-lg p-1 text-sm">
      <button
        onClick={() => onModeChange('geodesic')}
        className={`px-3 py-1.5 rounded-md transition-colors ${
          mode === 'geodesic'
            ? 'bg-white text-blue-700 shadow-sm font-medium'
            : 'text-gray-500 hover:text-gray-700'
        }`}
      >
        Geodésico
      </button>
      <button
        onClick={() => vialAvailable && onModeChange('vial')}
        disabled={!vialAvailable}
        className={`px-3 py-1.5 rounded-md transition-colors ${
          mode === 'vial'
            ? 'bg-white text-blue-700 shadow-sm font-medium'
            : vialAvailable
              ? 'text-gray-500 hover:text-gray-700'
              : 'text-gray-300 cursor-not-allowed'
        }`}
      >
        Vial
      </button>
    </div>
  );
}
