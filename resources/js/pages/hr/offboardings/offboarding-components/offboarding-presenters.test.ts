import { describe, expect, it } from 'vitest';
import { canFinalizeOffboarding } from './offboarding-finalize-dialog';
import { offboardingExitTypeLabel, offboardingStatusLabel, offboardingTaskStatusLabel } from './offboarding-presenters';

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
});
