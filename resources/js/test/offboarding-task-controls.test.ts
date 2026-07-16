import { availableOffboardingTaskActions } from '@/pages/hr/offboardings/offboarding-components/offboarding-task-controls';
import { describe, expect, it } from 'vitest';

describe('availableOffboardingTaskActions', () => {
    it('mengikuti boundary assignment dan completion Task 08', () => {
        expect(availableOffboardingTaskActions('DRAFT', 'PENDING', true, false, true, false)).toEqual({
            assign: true,
            start: false,
            complete: false,
            skip: false,
            reopen: false,
        });
        expect(availableOffboardingTaskActions('IN_PROGRESS', 'PENDING', false, false, true, false)).toEqual({
            assign: true,
            start: true,
            complete: false,
            skip: true,
            reopen: false,
        });
        expect(availableOffboardingTaskActions('IN_PROGRESS', 'IN_PROGRESS', false, false, true, false)).toEqual({
            assign: true,
            start: false,
            complete: true,
            skip: true,
            reopen: false,
        });
        expect(availableOffboardingTaskActions('IN_PROGRESS', 'PENDING', true, false, true, false).skip).toBe(false);
        expect(availableOffboardingTaskActions('IN_PROGRESS', 'PENDING', true, false, true, true).skip).toBe(true);
        expect(availableOffboardingTaskActions('READY_FOR_EXIT', 'COMPLETED', true, false, true, true).reopen).toBe(true);
        expect(availableOffboardingTaskActions('IN_PROGRESS', 'COMPLETED', true, false, true, true).assign).toBe(false);
        expect(availableOffboardingTaskActions('COMPLETED', 'PENDING', false, false, true, true).assign).toBe(false);
        expect(availableOffboardingTaskActions('IN_PROGRESS', 'PENDING', false, true, true, true).start).toBe(false);
        expect(availableOffboardingTaskActions('IN_PROGRESS', 'PENDING', false, false, false, true).start).toBe(false);
    });
});
