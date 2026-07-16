import { canActivateOffboarding } from '@/pages/hr/offboardings/offboarding-components/offboarding-activation-dialog';
import { describe, expect, it } from 'vitest';

describe('canActivateOffboarding', () => {
    it('hanya menampilkan activation untuk draft aktif dan user berizin', () => {
        expect(canActivateOffboarding('DRAFT', false, true)).toBe(true);
        expect(canActivateOffboarding('IN_PROGRESS', false, true)).toBe(false);
        expect(canActivateOffboarding('DRAFT', true, true)).toBe(false);
        expect(canActivateOffboarding('DRAFT', false, false)).toBe(false);
    });
});
