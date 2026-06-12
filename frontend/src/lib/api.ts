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
