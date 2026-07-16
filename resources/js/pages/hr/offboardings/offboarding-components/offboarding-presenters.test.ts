import { describe, expect, it } from 'vitest';
import { canFinalizeOffboarding } from './offboarding-finalize-dialog';
import {
    emptyOffboardingMessage,
    hasActiveOffboardingFilters,
    offboardingExitTypeLabel,
    offboardingStatusLabel,
    offboardingTaskStatusLabel,
} from './offboarding-presenters';

describe('offboarding presenters', () => {
    it('maps known lifecycle values to simple Indonesian labels', () => {
        expect(offboardingStatusLabel('READY_FOR_EXIT')).toBe('Siap keluar');
        expect(offboardingTaskStatusLabel('SKIPPED')).toBe('Dilewati');
        expect(offboardingExitTypeLabel('END_OF_CONTRACT')).toBe('Kontrak berakhir');
    });

    it('keeps unknown values visible instead of hiding them', () => {
        expect(offboardingStatusLabel('UNKNOWN')).toBe('UNKNOWN');
    });

    it('shows finalization only for permitted ready cases on or after exit date', () => {
        expect(canFinalizeOffboarding('READY_FOR_EXIT', false, '2026-07-20', '2026-07-20', true)).toBe(true);
        expect(canFinalizeOffboarding('READY_FOR_EXIT', false, '2026-07-19', '2026-07-20', true)).toBe(false);
        expect(canFinalizeOffboarding('IN_PROGRESS', false, '2026-07-20', '2026-07-20', true)).toBe(false);
        expect(canFinalizeOffboarding('READY_FOR_EXIT', true, '2026-07-20', '2026-07-20', true)).toBe(false);
        expect(canFinalizeOffboarding('READY_FOR_EXIT', false, '2026-07-20', '2026-07-20', false)).toBe(false);
    });

    it('detects meaningful filters while ignoring only the business date', () => {
        expect(hasActiveOffboardingFilters({ business_date: '2026-07-17' })).toBe(false);
        expect(hasActiveOffboardingFilters({ business_date: '2026-07-17', archived: false, status: '' })).toBe(false);
        expect(hasActiveOffboardingFilters({ business_date: '2026-07-17', overdue: true })).toBe(true);
        expect(hasActiveOffboardingFilters({ business_date: '2026-07-17', status: 'COMPLETED' })).toBe(true);
    });

    it('explains empty states for default, filtered, and archived lists', () => {
        expect(emptyOffboardingMessage(false, false)).toContain('Buat draft pertama');
        expect(emptyOffboardingMessage(true, false)).toContain('cocok dengan filter');
        expect(emptyOffboardingMessage(false, true)).toContain('histori offboarding');
    });
});
