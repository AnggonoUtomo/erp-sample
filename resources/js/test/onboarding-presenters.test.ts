import { describe, expect, it } from 'vitest';
import {
    emptyOnboardingMessage,
    hasActiveOnboardingFilters,
    onboardingStatusLabel,
    onboardingTaskStatusLabel,
} from '../pages/hr/onboardings/onboarding-components/onboarding-presenters';

describe('onboarding presenters', () => {
    it('maps every supported state to a simple Indonesian label', () => {
        expect(onboardingStatusLabel('DRAFT')).toBe('Draft');
        expect(onboardingStatusLabel('IN_PROGRESS')).toBe('Sedang berjalan');
        expect(onboardingStatusLabel('COMPLETED')).toBe('Selesai');
        expect(onboardingStatusLabel('CANCELLED')).toBe('Dibatalkan');
        expect(onboardingTaskStatusLabel('PENDING')).toBe('Belum dimulai');
        expect(onboardingTaskStatusLabel('SKIPPED')).toBe('Dilewati');
    });

    it('distinguishes a filtered empty result from an empty module', () => {
        expect(hasActiveOnboardingFilters({ status: 'IN_PROGRESS' })).toBe(true);
        expect(hasActiveOnboardingFilters({ business_date: '2026-07-15' })).toBe(false);
        expect(emptyOnboardingMessage(true, false)).toContain('filter');
        expect(emptyOnboardingMessage(false, true)).toContain('arsip');
        expect(emptyOnboardingMessage(false, false)).toContain('Belum ada');
    });
});
