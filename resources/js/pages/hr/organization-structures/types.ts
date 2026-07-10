import type { FormDataConvertible } from '@inertiajs/core';

export type SimpleOption = { value: number | string; label: string };
export type RelatedItem = { id: number; code: string; name: string } | null;

export type OrganizationStructureRow = {
    id: number;
    parent_id: number | null;
    departement_id: number | null;
    position_id: number | null;
    code: string;
    name: string;
    node_type: string;
    description: string | null;
    active: boolean;
    sort_order: number;
    active_children_count: number;
    parent: RelatedItem;
    departement: RelatedItem;
    position: RelatedItem;
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

export type OrganizationStructureFilters = {
    search: string;
    node_type: string;
    status: string;
    archive: string;
    per_page: number;
};

export type OrganizationStructureSummary = {
    total: number;
    active: number;
    root_nodes: number;
    position_nodes: number;
    archived: number;
};

export interface OrganizationStructureForm extends Record<string, FormDataConvertible> {
    parent_id: string;
    departement_id: string;
    position_id: string;
    code: string;
    name: string;
    node_type: string;
    description: string;
    active: boolean;
}

export type OrganizationStructurePageProps = {
    organizationStructures: Paginator<OrganizationStructureRow>;
    parentOptions: SimpleOption[];
    departementOptions: SimpleOption[];
    positionOptions: SimpleOption[];
    nodeTypeOptions: SimpleOption[];
    filters: OrganizationStructureFilters;
    summary: OrganizationStructureSummary;
};
