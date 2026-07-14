import { OnboardingSummaryCards } from '@/pages/hr/onboardings/onboarding-components/onboarding-summary-cards';
import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';

describe('OnboardingSummaryCards', () => {
    it('renders the deterministic progress breakdown', () => {
        render(
            <OnboardingSummaryCards
                progress={{ total: 4, terminal: 2, completed: 1, required_incomplete: 1, optional_incomplete: 1, overdue: 1, percentage: 50 }}
            />,
        );

        expect(screen.getByText('50%')).toBeTruthy();
        expect(screen.getByText('Completed')).toBeTruthy();
        expect(screen.getByText('Wajib tersisa')).toBeTruthy();
        expect(screen.getByText('Opsional tersisa')).toBeTruthy();
        expect(screen.getByText('Terlambat')).toBeTruthy();
    });
});
