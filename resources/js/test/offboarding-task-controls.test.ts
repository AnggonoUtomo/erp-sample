import { availableOffboardingTaskActions } from '@/pages/hr/offboardings/offboarding-components/offboarding-task-controls';
import { describe, expect, it } from 'vitest';

describe('availableOffboardingTaskActions', () => {
    it('mengikuti boundary assignment dan completion Task 08', () => {
        expect(availableOffboardingTaskActions('DRAFT', 'PENDING', false, true)).toEqual({
            assign: true,
            start: false,
            complete: false,
        });
        expect(availableOffboardingTaskActions('IN_PROGRESS', 'PENDING', false, true)).toEqual({
            assign: true,
            start: true,
            complete: false,
        });
        expect(availableOffboardingTaskActions('IN_PROGRESS', 'IN_PROGRESS', false, true)).toEqual({
            assign: true,
            start: false,
            complete: true,
        });
        expect(availableOffboardingTaskActions('IN_PROGRESS', 'COMPLETED', false, true).assign).toBe(false);
        expect(availableOffboardingTaskActions('COMPLETED', 'PENDING', false, true).assign).toBe(false);
        expect(availableOffboardingTaskActions('IN_PROGRESS', 'PENDING', true, true).start).toBe(false);
        expect(availableOffboardingTaskActions('IN_PROGRESS', 'PENDING', false, false).start).toBe(false);
    });
});
