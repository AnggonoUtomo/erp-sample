import { describe, expect, it } from 'vitest';
import { canCancelOnboarding, canCompleteOnboarding } from '../pages/hr/onboardings/onboarding-components/onboarding-lifecycle-dialogs';

describe('onboarding lifecycle controls', () => {
    it('only enables completion for permitted active onboarding without required incomplete tasks', () => {
        expect(canCompleteOnboarding('IN_PROGRESS', false, 0, true)).toBe(true);
        expect(canCompleteOnboarding('IN_PROGRESS', false, 1, true)).toBe(false);
        expect(canCompleteOnboarding('COMPLETED', false, 0, true)).toBe(false);
    });

    it('only enables cancellation for permitted draft or in-progress onboarding', () => {
        expect(canCancelOnboarding('DRAFT', false, true)).toBe(true);
        expect(canCancelOnboarding('IN_PROGRESS', false, true)).toBe(true);
        expect(canCancelOnboarding('CANCELLED', false, true)).toBe(false);
    });
});
