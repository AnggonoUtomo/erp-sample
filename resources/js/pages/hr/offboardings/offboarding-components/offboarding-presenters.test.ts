import { describe, expect, it } from 'vitest';
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
});
