const onboardingLabels: Record<string, string> = {
    DRAFT: 'Draft',
    IN_PROGRESS: 'Sedang berjalan',
    COMPLETED: 'Selesai',
    CANCELLED: 'Dibatalkan',
};

const taskLabels: Record<string, string> = {
    PENDING: 'Belum dimulai',
    IN_PROGRESS: 'Sedang dikerjakan',
    COMPLETED: 'Selesai',
    SKIPPED: 'Dilewati',
};

export const onboardingStatusLabel = (status: string) => onboardingLabels[status] ?? status;
export const onboardingTaskStatusLabel = (status: string) => taskLabels[status] ?? status;

export function hasActiveOnboardingFilters(filters: Record<string, unknown>): boolean {
    return Object.entries(filters).some(([key, value]) => key !== 'business_date' && value !== '' && value !== false && value != null);
}

export function emptyOnboardingMessage(filtered: boolean, archived: boolean): string {
    if (filtered) return 'Tidak ada onboarding yang cocok dengan filter. Ubah atau reset filter untuk melihat data lain.';
    if (archived) return 'Belum ada histori onboarding yang diarsipkan.';
    return 'Belum ada onboarding. Buat draft pertama untuk memulai checklist employee.';
}
