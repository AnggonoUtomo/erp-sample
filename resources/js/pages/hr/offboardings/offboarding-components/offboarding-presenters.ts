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
