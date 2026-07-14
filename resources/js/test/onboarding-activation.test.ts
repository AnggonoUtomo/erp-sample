import { canActivateOnboarding } from '@/pages/hr/onboardings/onboarding-components/onboarding-activation-dialog';
import { describe, expect, it } from 'vitest';

describe('canActivateOnboarding', () => {
    it('only allows an authorized non-archived draft', () => {
        expect(canActivateOnboarding('DRAFT', false, true)).toBe(true);
        expect(canActivateOnboarding('IN_PROGRESS', false, true)).toBe(false);
        expect(canActivateOnboarding('DRAFT', true, true)).toBe(false);
        expect(canActivateOnboarding('DRAFT', false, false)).toBe(false);
    });
});
