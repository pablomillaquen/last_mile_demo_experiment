'use client';

interface ViewModeToggleProps {
  viewMode: 'simple' | 'split';
  onViewModeChange: (mode: 'simple' | 'split') => void;
  splitAvailable: boolean;
}

export default function ViewModeToggle({ viewMode, onViewModeChange, splitAvailable }: ViewModeToggleProps) {
  return (
    <div className="flex items-center gap-1 bg-gray-100 rounded-lg p-1 text-sm">
      <button
        onClick={() => onViewModeChange('simple')}
        className={`px-3 py-1.5 rounded-md transition-colors ${
          viewMode === 'simple'
            ? 'bg-white text-blue-700 shadow-sm font-medium'
            : 'text-gray-500 hover:text-gray-700'
        }`}
      >
        Vista simple
      </button>
      <button
        onClick={() => splitAvailable && onViewModeChange('split')}
        disabled={!splitAvailable}
        className={`px-3 py-1.5 rounded-md transition-colors ${
          viewMode === 'split'
            ? 'bg-white text-blue-700 shadow-sm font-medium'
            : splitAvailable
              ? 'text-gray-500 hover:text-gray-700'
              : 'text-gray-300 cursor-not-allowed'
        }`}
        title={!splitAvailable ? 'No hay datos viales para esta evaluación' : 'Vista comparativa'}
      >
        Comparativa
      </button>
    </div>
  );
}
