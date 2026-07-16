import { canMarkOffboardingReady } from '@/pages/hr/offboardings/offboarding-components/offboarding-ready-dialog';
import { describe, expect, it } from 'vitest';

describe('canMarkOffboardingReady', () => {
    it('hanya tersedia untuk in-progress tanpa required incomplete dan user berizin', () => {
        expect(canMarkOffboardingReady('IN_PROGRESS', false, 0, true)).toBe(true);
        expect(canMarkOffboardingReady('IN_PROGRESS', false, 1, true)).toBe(false);
        expect(canMarkOffboardingReady('READY_FOR_EXIT', false, 0, true)).toBe(false);
        expect(canMarkOffboardingReady('IN_PROGRESS', true, 0, true)).toBe(false);
        expect(canMarkOffboardingReady('IN_PROGRESS', false, 0, false)).toBe(false);
    });
});
