import { useHRReferenceDataPage } from '@/pages/hr/hr-reference-data/use-hr-reference-data-page';
import { act, renderHook } from '@testing-library/react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const { canAny, routerGet } = vi.hoisted(() => ({ canAny: vi.fn(() => true), routerGet: vi.fn() }));

vi.mock('@/hooks/use-permission', () => ({
    usePermission: () => ({ canAny }),
}));

vi.mock('@inertiajs/react', async (importOriginal) => {
    const original = await importOriginal<typeof import('@inertiajs/react')>();

    return {
        ...original,
        router: { ...original.router, get: routerGet },
    };
});

const pageState = {
    filters: { search: '', category: 'all', status: 'all', archive: 'active' },
    referenceData: { per_page: 15 },
} as Parameters<typeof useHRReferenceDataPage>[0];

describe('useHRReferenceDataPage', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        canAny.mockReturnValue(true);
        routerGet.mockClear();
        vi.stubGlobal(
            'route',
            vi.fn((name: string) => name),
        );
    });

    afterEach(() => {
        vi.useRealTimers();
        vi.unstubAllGlobals();
    });

    it('reloads the page with filters after the debounce interval', () => {
        const { result } = renderHook(() => useHRReferenceDataPage(pageState));

        act(() => result.current.setSearch('bank'));
        expect(routerGet).not.toHaveBeenCalled();

        act(() => vi.advanceTimersByTime(400));

        expect(routerGet).toHaveBeenCalledWith(
            'hr.hr-reference-data.index',
            { search: 'bank', category: 'all', status: 'all', archive: 'active', per_page: 15 },
            { preserveScroll: true, preserveState: true, replace: true },
        );
    });

    it('focuses search with slash and opens create mode with the create shortcut', () => {
        const searchInput = document.createElement('input');
        searchInput.id = 'hr-reference-data-search-input';
        document.body.append(searchInput);
        const { result } = renderHook(() => useHRReferenceDataPage(pageState));

        act(() => window.dispatchEvent(new KeyboardEvent('keydown', { key: '/' })));
        expect(document.activeElement).toBe(searchInput);

        act(() => window.dispatchEvent(new KeyboardEvent('keydown', { key: 'A', ctrlKey: true, shiftKey: true })));
        expect(result.current.workspaceMode).toBe('create');
    });

    it('derives action availability from the current permissions', () => {
        canAny.mockReturnValue(false);

        const { result } = renderHook(() => useHRReferenceDataPage(pageState));

        expect(result.current.canCreate).toBe(false);
        expect(result.current.canUpdate).toBe(false);
        expect(result.current.canDelete).toBe(false);
        expect(result.current.canRestore).toBe(false);
        expect(result.current.canForceDelete).toBe(false);
    });

    it('hydrates the form when an item enters edit mode', () => {
        const row = {
            id: 7,
            category: 'bank',
            code: 'BCA',
            name: 'Bank Central Asia',
            description: null,
            active: true,
            deleted_at: null,
        } as Parameters<ReturnType<typeof useHRReferenceDataPage>['startEdit']>[0];
        const { result } = renderHook(() => useHRReferenceDataPage(pageState));

        act(() => result.current.startEdit(row));

        expect(result.current.workspaceMode).toBe('edit');
        expect(result.current.editing).toBe(row);
        expect(result.current.form.data).toEqual({
            category: 'bank',
            code: 'BCA',
            name: 'Bank Central Asia',
            description: '',
            active: true,
        });
    });
});
