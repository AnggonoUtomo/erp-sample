import type { FormDataConvertible } from '@inertiajs/core';

export type OffboardingTemplateItem = {
    id: number;
    title: string;
    description: string | null;
    category: string;
    required: boolean;
    due_offset_days: number;
    default_assignee_role: string | null;
    sort_order: number;
};

export type OffboardingTemplate = {
    id: number;
    code: string;
    name: string;
    description: string | null;
    active: boolean;
    items: OffboardingTemplateItem[];
};

export type TemplatePaginator = {
    data: OffboardingTemplate[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    total: number;
};

export type TemplateItemForm = {
    title: string;
    description: string;
    category: string;
    required: boolean;
    due_offset_days: number;
    default_assignee_role: string;
};

export interface TemplateForm extends Record<string, FormDataConvertible> {
    code: string;
    name: string;
    description: string;
    active: boolean;
    items: TemplateItemForm[];
}
