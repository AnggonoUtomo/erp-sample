const statusLabels: Record<string, string> = {
    DRAFT: 'Draft',
    IN_PROGRESS: 'Berjalan',
    READY_FOR_EXIT: 'Siap keluar',
    COMPLETED: 'Selesai',
    CANCELLED: 'Dibatalkan',
};

const taskStatusLabels: Record<string, string> = {
    PENDING: 'Menunggu',
    IN_PROGRESS: 'Berjalan',
    COMPLETED: 'Selesai',
    SKIPPED: 'Dilewati',
};

const exitTypeLabels: Record<string, string> = {
    RESIGNATION: 'Pengunduran diri',
    TERMINATION: 'Pemutusan hubungan kerja',
    END_OF_CONTRACT: 'Kontrak berakhir',
    RETIREMENT: 'Pensiun',
    OTHER: 'Lainnya',
};

export function offboardingStatusLabel(status: string): string {
    return statusLabels[status] ?? status;
}

export function offboardingTaskStatusLabel(status: string): string {
    return taskStatusLabels[status] ?? status;
}

export function offboardingExitTypeLabel(type: string): string {
    return exitTypeLabels[type] ?? type;
}

export function hasActiveOffboardingFilters(filters: Record<string, unknown>): boolean {
    return Object.entries(filters).some(([key, value]) => key !== 'business_date' && value !== '' && value !== false && value != null);
}

export function emptyOffboardingMessage(filtered: boolean, archived: boolean): string {
    if (filtered) return 'Tidak ada offboarding yang cocok dengan filter. Ubah atau reset filter untuk melihat data lain.';
    if (archived) return 'Belum ada histori offboarding yang diarsipkan.';
    return 'Belum ada offboarding. Buat draft pertama untuk memulai proses exit employee.';
}
