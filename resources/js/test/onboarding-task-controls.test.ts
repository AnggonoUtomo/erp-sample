import { availableTaskActions } from '@/pages/hr/onboardings/onboarding-components/onboarding-task-controls';
import { describe, expect, it } from 'vitest';

describe('availableTaskActions', () => {
    it('follows onboarding and task lifecycle boundaries', () => {
        expect(availableTaskActions('DRAFT', 'PENDING', true, false, true, false)).toEqual({
            assign: true,
            start: false,
            complete: false,
            skip: false,
            reopen: false,
        });
        expect(availableTaskActions('IN_PROGRESS', 'PENDING', false, false, true, false)).toEqual({
            assign: true,
            start: true,
            complete: false,
            skip: true,
            reopen: false,
        });
        expect(availableTaskActions('IN_PROGRESS', 'PENDING', true, false, true, false).skip).toBe(false);
        expect(availableTaskActions('IN_PROGRESS', 'PENDING', true, false, true, true).skip).toBe(true);
        expect(availableTaskActions('IN_PROGRESS', 'IN_PROGRESS', false, false, true, false).complete).toBe(true);
        expect(availableTaskActions('IN_PROGRESS', 'COMPLETED', true, false, true, false).reopen).toBe(true);
        expect(availableTaskActions('IN_PROGRESS', 'COMPLETED', true, false, true, true).assign).toBe(false);
        expect(availableTaskActions('COMPLETED', 'PENDING', false, false, true, true).assign).toBe(false);
        expect(availableTaskActions('IN_PROGRESS', 'PENDING', false, true, true, true).skip).toBe(false);
        expect(availableTaskActions('IN_PROGRESS', 'PENDING', false, false, false, true).skip).toBe(false);
    });
});
