import { canCancelOffboarding } from '@/pages/hr/offboardings/offboarding-components/offboarding-cancel-dialog';
import { describe, expect, it } from 'vitest';

describe('canCancelOffboarding', () => {
    it('hanya tersedia untuk case aktif non-archived dan user berizin', () => {
        expect(canCancelOffboarding('DRAFT', false, true)).toBe(true);
        expect(canCancelOffboarding('IN_PROGRESS', false, true)).toBe(true);
        expect(canCancelOffboarding('READY_FOR_EXIT', false, true)).toBe(true);
        expect(canCancelOffboarding('COMPLETED', false, true)).toBe(false);
        expect(canCancelOffboarding('CANCELLED', false, true)).toBe(false);
        expect(canCancelOffboarding('IN_PROGRESS', true, true)).toBe(false);
        expect(canCancelOffboarding('IN_PROGRESS', false, false)).toBe(false);
    });
});
