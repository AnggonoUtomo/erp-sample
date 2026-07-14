import type { FormDataConvertible } from '@inertiajs/core';

export type TemplateItem = {
    id: number;
    title: string;
    description: string | null;
    category: string;
    required: boolean;
    due_offset_days: number;
    default_assignee_role: string | null;
    sort_order: number;
};

export type OnboardingTemplate = {
    id: number;
    code: string;
    name: string;
    description: string | null;
    active: boolean;
    items: TemplateItem[];
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
