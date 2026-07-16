import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User | null;
    roles: Record<string, boolean>;
    permissions: Record<string, boolean>;
    super: boolean;
    impersonation: {
        active: boolean;
        impersonator: {
            id: number;
            name: string;
            email: string;
        } | null;
    };
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface Branding {
    app_name: string;
    logo_url: string | null;
    favicon_url: string | null;
}

export interface Localization {
    timezone: string;
    date_format: string;
    time_format: string;
    datetime_format: string;
    preview_date: string;
    preview_time: string;
    preview_datetime: string;
}

export interface PaginationSetting {
    default_per_page: number;
    per_page_options: number[];
}

export interface ActivityCenterItem {
    id: number;
    module: string;
    event: string;
    description: string | null;
    actor: {
        id: number;
        name: string;
        email: string;
    } | null;
    created_at: string | null;
    created_at_human: string | null;
    unread: boolean;
}

export interface ActivityCenter {
    unread_count: number;
    read_at: string | null;
    items: ActivityCenterItem[];
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    url: string;
    icon?: LucideIcon | string | null;
    isActive?: boolean;
    badge?: string;
    permissions?: string[];
    children?: NavItem[];
    exact?: boolean;
}

export interface NavigationGroup {
    group: string;
    sort?: number;
    items: NavItem[];
}

export interface SharedData {
    name: string;
    branding: Branding;
    localization: Localization;
    pagination: PaginationSetting;
    navigation: NavigationGroup[];
    activity_center: ActivityCenter;
    quote: { message: string; author: string };
    flash?: {
        success?: string | null;
        error?: string | null;
    };
    auth: Auth;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}
