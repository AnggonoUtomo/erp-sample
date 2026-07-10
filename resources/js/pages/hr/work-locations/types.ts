import type { FormDataConvertible } from '@inertiajs/core';

export type WorkLocationRow = {
    id: number;
    code: string;
    name: string;
    address: string | null;
    city: string | null;
    province: string | null;
    country: string;
    postal_code: string | null;
    timezone: string;
    latitude: number | null;
    longitude: number | null;
    geofence_radius_meters: number | null;
    description: string | null;
    active: boolean;
    sort_order: number;
    deleted_at: string | null;
    created_at: string | null;
    updated_at: string | null;
};

export type Paginator<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
};

export type WorkLocationFilters = {
    search: string;
    status: string;
    archive: string;
    city: string;
    per_page: number;
};

export type WorkLocationSummary = {
    total: number;
    active: number;
    inactive: number;
    archived: number;
};

export interface WorkLocationForm extends Record<string, FormDataConvertible> {
    code: string;
    name: string;
    address: string;
    city: string;
    province: string;
    country: string;
    postal_code: string;
    timezone: string;
    latitude: string;
    longitude: string;
    geofence_radius_meters: string;
    description: string;
    active: boolean;
}

export type WorkLocationPageProps = {
    workLocations: Paginator<WorkLocationRow>;
    cityOptions: string[];
    filters: WorkLocationFilters;
    summary: WorkLocationSummary;
    mapSettings: {
        enabled: boolean;
        google_maps_api_key: string | null;
        google_maps_map_id: string | null;
        configured: boolean;
    };
};
