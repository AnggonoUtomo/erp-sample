export type BackupOverview = {
    system_settings: number;
    encrypted_settings: number;
    notification_templates: number;
    database_connection: string;
    database_name: string;
    storage_public_exists: boolean;
    storage_public_size: number;
    last_ready_at: string;
    included_sections: string[];
    excluded_sections: string[];
};

export type BackupRestoreAbilities = {
    export: boolean;
    restore: boolean;
    fullExport: boolean;
    fullRestore: boolean;
};

export type RestoreForm = {
    backup: File | null;
    restore_system_settings: boolean;
    restore_notification_templates: boolean;
};

export type FullRestoreForm = {
    backup: File | null;
    restore_database: boolean;
    restore_storage_public: boolean;
    dry_run: boolean;
    confirmation: string;
};

export function sectionLabel(section: string) {
    return section.replaceAll('_', ' ');
}
