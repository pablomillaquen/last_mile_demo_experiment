const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

async function request<T>(path: string, options?: RequestInit): Promise<T> {
  const res = await fetch(`${API_BASE}${path}`, {
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    ...options,
  });
  if (!res.ok) {
    const error = await res.text();
    throw new Error(`HTTP ${res.status}: ${error}`);
  }
  if (res.status === 204) return undefined as T;
  return res.json();
}

export interface Package {
  id: number;
  tracking_number: string;
  recipient_name: string;
  delivery_address: string;
  district: string | null;
  city: string | null;
  latitude: number;
  longitude: number;
  assigned: boolean;
  received_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    total: number;
  };
}

export interface Route {
  id: number;
  name: string;
  route_date: string;
  notes: string | null;
  route_packages_count?: number;
  total_distance_km?: number;
  avg_distance_per_delivery_km?: number;
  estimated_time?: string;
  deliveries_count?: number;
  route_packages?: RoutePackage[];
  created_at: string;
  updated_at: string;
}

export interface RoutePackage {
  id: number;
  route_id: number;
  package_id: number;
  sequence: number | null;
  assigned_at: string | null;
  package?: Package;
}

export const routesApi = {
  list: (params?: { page?: number }) => {
    const q = new URLSearchParams();
    if (params?.page) q.set('page', String(params.page));
    return request<PaginatedResponse<Route>>(`/routes?${q}`);
  },
  get: (id: number) => request<Route>(`/routes/${id}`),
  create: (data: { name: string; route_date: string; notes?: string }) =>
    request<Route>('/routes', { method: 'POST', body: JSON.stringify(data) }),
  update: (id: number, data: Partial<Route>) =>
    request<Route>(`/routes/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  delete: (id: number) => request<void>(`/routes/${id}`, { method: 'DELETE' }),
  assign: (id: number, packageId: number, sequence?: number) =>
    request<RoutePackage>(`/routes/${id}/assign`, { method: 'POST', body: JSON.stringify({ package_id: packageId, sequence }) }),
  unassign: (id: number, packageId: number) =>
    request<void>(`/routes/${id}/unassign`, { method: 'POST', body: JSON.stringify({ package_id: packageId }) }),
};

export interface Settings {
  warehouse_lat: string;
  warehouse_lng: string;
  average_speed_kmh: string;
}

export const settingsApi = {
  get: () => request<Settings>('/settings'),
  update: (data: Partial<Settings>) =>
    request<Settings>('/settings', { method: 'PUT', body: JSON.stringify(data) }),
};

export const packagesApi = {
  list: (params?: { assigned?: boolean; page?: number; per_page?: number }) => {
    const q = new URLSearchParams();
    if (params?.assigned !== undefined) q.set('assigned', String(params.assigned));
    if (params?.page) q.set('page', String(params.page));
    if (params?.per_page) q.set('per_page', String(params.per_page));
    const query = q.toString();
    return request<PaginatedResponse<Package>>(`/packages${query ? '?' + query : ''}`);
  },
  get: (id: number) => request<Package>(`/packages/${id}`),
  create: (data: Omit<Package, 'id' | 'assigned' | 'created_at' | 'updated_at'>) =>
    request<Package>('/packages', { method: 'POST', body: JSON.stringify(data) }),
  update: (id: number, data: Partial<Package>) =>
    request<Package>(`/packages/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  delete: (id: number) => request<void>(`/packages/${id}`, { method: 'DELETE' }),
};

export interface MetricsSummary {
  coverage_territorial_km: number;
  distancia_promedio_general_km: number;
  desviacion_estandar_distancias_km: number;
  balance_general_cv: number;
  balance_index: number;
  inter_cluster_min_distance_km: number;
  total_anomalias_detectadas: number;
  operational_penalty_total: number;
}

export interface RouteMetric {
  route_id: number;
  route_name: string;
  total_deliveries: number;
  min_distance_to_warehouse_km: number;
  max_distance_to_warehouse_km: number;
  avg_distance_to_warehouse_km: number;
  centroid_lat: number;
  centroid_lng: number;
  centroid_to_warehouse_km: number;
  cluster_radius_km: number;
  avg_distance_to_centroid_km: number;
  estimated_route_distance_km: number;
}

export interface Anomaly {
  delivery_id: number;
  route_id: number;
  distance_to_warehouse_km: number;
  centroid_distance_km: number;
  ratio: number;
}

export interface RankingItem {
  rank: number;
  route_id: number;
  route_name: string;
  avg_distance_km: number;
}

export interface EvaluationFiles {
  json: string;
  csv: string;
  deliveries_csv: string;
  maps: {
    overview: string | null;
    routes: string[];
    anomalies: string | null;
  };
}

export interface Parameters {
  near_delivery_threshold_km: number;
  ignored_delivery_ratio: number;
  random_seed: number | null;
  algorithm: string;
  algorithm_version: string;
  warehouse_lat: number;
  warehouse_lng: number;
  dataset: string;
}

export interface Evaluation {
  id: number;
  executed_at: string;
  total_deliveries: number;
  total_routes: number;
  metrics_summary: MetricsSummary;
  output_path?: string;
  parameters?: Parameters;
  route_metrics?: RouteMetric[];
  anomalies?: Anomaly[];
  ranking?: RankingItem[];
  files?: EvaluationFiles;
}

const API_BASE_FILES = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

export const evaluationsApi = {
  list: () => request<{ data: Evaluation[] }>('/evaluations'),
  get: (id: number) => request<Evaluation>(`/evaluations/${id}`),
  run: (params?: {
    near_delivery_threshold_km?: number;
    ignored_delivery_ratio?: number;
    random_seed?: number | null;
    algorithm?: string;
    algorithm_version?: string;
  }) => request<Evaluation>('/evaluations', { method: 'POST', body: JSON.stringify(params || {}) }),
  fileUrl: (evaluationId: number, filename: string) =>
    `${API_BASE_FILES}/evaluations/${evaluationId}/files/${filename}`,
};
