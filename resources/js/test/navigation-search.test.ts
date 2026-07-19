import { buildNavigationSearchResults, filterNavigationSearchResults, moveCommandPaletteSelection } from '@/lib/navigation-search';
import { type NavigationGroup } from '@/types';
import { describe, expect, it } from 'vitest';

const navigation: NavigationGroup[] = [
    {
        group: 'Administrasi',
        items: [
            {
                title: 'User Management',
                url: '/users',
                permissions: ['users.view'],
            },
            {
                title: 'Access Control',
                url: '/access-control',
                permissions: ['access-control.view'],
            },
        ],
    },
    {
        group: 'Employee',
        items: [
            {
                title: 'Employee Onboardings',
                url: '/hr/onboardings',
                permissions: ['hr.onboardings.view'],
                children: [
                    {
                        title: 'Onboarding Templates',
                        url: '/hr/onboarding-templates',
                        permissions: ['hr.onboarding-templates.view'],
                    },
                ],
            },
            {
                title: 'Employee Documents',
                url: '/hr/employee-documents',
                permissions: ['hr.employee-documents.view'],
            },
        ],
    },
];

describe('navigation search', () => {
    it('flattens parent and nested navigation into permission-aware command results', () => {
        const results = buildNavigationSearchResults(navigation, {
            permissions: {
                'users.view': true,
                'hr.onboardings.view': true,
                'hr.onboarding-templates.view': true,
            },
        });

        expect(results.map((result) => result.url)).toEqual(['/users', '/hr/onboardings', '/hr/onboarding-templates']);
        expect(results.find((result) => result.url === '/hr/onboardings')).toMatchObject({
            title: 'Onboarding Karyawan',
            group: 'Karyawan',
        });
        expect(results.find((result) => result.url === '/hr/onboarding-templates')?.keywords).toContain('Template Onboarding');
    });

    it('excludes navigation without granted permissions', () => {
        const results = buildNavigationSearchResults(navigation, {
            permissions: {
                'hr.employee-documents.view': true,
            },
        });

        expect(results.map((result) => result.url)).toEqual(['/hr/employee-documents']);
        expect(results.map((result) => result.url)).not.toContain('/access-control');
    });

    it('allows a result when any permission in its permission array is granted', () => {
        const results = buildNavigationSearchResults(
            [
                {
                    group: 'Administrasi',
                    items: [
                        {
                            title: 'User Management',
                            url: '/users',
                            permissions: ['users.manage', 'users.view'],
                        },
                        {
                            title: 'Access Control',
                            url: '/access-control',
                            permissions: ['access-control.view'],
                        },
                    ],
                },
            ],
            {
                permissions: {
                    'users.view': true,
                },
            },
        );

        expect(results.map((result) => result.url)).toEqual(['/users']);
    });

    it('rejects results without safe internal urls', () => {
        const results = buildNavigationSearchResults(
            [
                {
                    group: 'Administrasi',
                    items: [
                        {
                            title: 'Safe Internal',
                            url: '/safe-internal',
                            permissions: ['safe.view'],
                        },
                        {
                            title: 'External',
                            url: 'https://example.test/users',
                            permissions: ['safe.view'],
                        },
                        {
                            title: 'Protocol Relative',
                            url: '//example.test/users',
                            permissions: ['safe.view'],
                        },
                        {
                            title: 'Script Link',
                            url: 'javascript:alert(1)',
                            permissions: ['safe.view'],
                        },
                    ],
                },
            ],
            {
                permissions: {
                    'safe.view': true,
                },
            },
        );

        expect(results.map((result) => result.url)).toEqual(['/safe-internal']);
    });

    it('rejects results that contain sensitive fields or keywords', () => {
        const results = buildNavigationSearchResults(
            [
                {
                    group: 'Sistem',
                    items: [
                        {
                            title: 'System Settings',
                            url: '/system-settings',
                            permissions: ['system-settings.view'],
                        },
                        {
                            title: 'API Token Vault',
                            url: '/api-token-vault',
                            permissions: ['system-settings.view'],
                        },
                        {
                            title: 'Password Reset',
                            url: '/password-reset',
                            permissions: ['system-settings.view'],
                        },
                        {
                            title: 'Secret Badge',
                            url: '/secret-badge',
                            badge: 'secret',
                            permissions: ['system-settings.view'],
                        },
                        {
                            title: 'Map Key',
                            url: '/system-settings?api_key=hidden',
                            permissions: ['system-settings.view'],
                        },
                    ],
                },
            ],
            {
                permissions: {
                    'system-settings.view': true,
                },
            },
        );

        expect(results.map((result) => result.url)).toEqual(['/system-settings']);
        expect(JSON.stringify(results).toLowerCase()).not.toMatch(/secret|token|password|api_key/);
    });

    it('filters by translated title, original title, group, and url', () => {
        const results = buildNavigationSearchResults(navigation, {
            permissions: {
                'users.view': true,
                'hr.onboardings.view': true,
                'hr.onboarding-templates.view': true,
                'hr.employee-documents.view': true,
            },
        });

        expect(filterNavigationSearchResults(results, 'karyawan').map((result) => result.url)).toEqual([
            '/hr/employee-documents',
            '/hr/onboardings',
            '/hr/onboarding-templates',
        ]);
        expect(filterNavigationSearchResults(results, 'User').map((result) => result.url)).toEqual(['/users']);
        expect(filterNavigationSearchResults(results, 'templates').map((result) => result.url)).toEqual(['/hr/onboarding-templates']);
    });

    it('ranks exact matches before prefix and contains matches deterministically', () => {
        const results = [
            {
                id: 'contains',
                title: 'Console User Activity',
                group: 'Observabilitas',
                url: '/login-activities',
                keywords: [],
            },
            {
                id: 'prefix',
                title: 'User Management',
                group: 'Administrasi',
                url: '/users',
                keywords: [],
            },
            {
                id: 'exact',
                title: 'User',
                group: 'Administrasi',
                url: '/users-shortcut',
                keywords: [],
            },
        ];

        expect(filterNavigationSearchResults(results, 'user').map((result) => result.id)).toEqual(['exact', 'prefix', 'contains']);
    });

    it('wraps keyboard selection through result boundaries', () => {
        expect(moveCommandPaletteSelection(0, 3, 'next')).toBe(1);
        expect(moveCommandPaletteSelection(2, 3, 'next')).toBe(0);
        expect(moveCommandPaletteSelection(0, 3, 'previous')).toBe(2);
        expect(moveCommandPaletteSelection(0, 0, 'next')).toBe(0);
    });
});
