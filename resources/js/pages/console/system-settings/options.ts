import type { MaintenanceStyle, RetryUnit } from './types';

export const timezoneOptions = [
    'Asia/Jakarta',
    'Asia/Makassar',
    'Asia/Jayapura',
    'UTC',
    'Asia/Singapore',
    'Asia/Kuala_Lumpur',
    'Asia/Tokyo',
    'Australia/Sydney',
    'Europe/London',
    'America/New_York',
];

export const dateFormatOptions = [
    { value: 'd M Y', label: '31 Jan 2026' },
    { value: 'd/m/Y', label: '31/01/2026' },
    { value: 'Y-m-d', label: '2026-01-31' },
    { value: 'm/d/Y', label: '01/31/2026' },
    { value: 'l, d F Y', label: 'Saturday, 31 January 2026' },
];

export const timeFormatOptions = [
    { value: 'H:i', label: '23:45' },
    { value: 'H:i:s', label: '23:45:30' },
    { value: 'h:i A', label: '11:45 PM' },
    { value: 'h:i:s A', label: '11:45:30 PM' },
];

export const perPageOptions = [5, 10, 15, 25, 50, 100];

export const retryUnitOptions: { value: RetryUnit; label: string; multiplier: number }[] = [
    { value: 'days', label: 'Hari', multiplier: 86400 },
    { value: 'hours', label: 'Jam', multiplier: 3600 },
    { value: 'minutes', label: 'Menit', multiplier: 60 },
    { value: 'seconds', label: 'Detik', multiplier: 1 },
];

export const retryMinimumSeconds = 30;
export const retryMaximumSeconds = 2592000;

export const maintenanceStyleOptions: { value: MaintenanceStyle; label: string; description: string }[] = [
    {
        value: 'aurora',
        label: 'Aurora',
        description: 'Halaman modern dengan aksen visual dan status yang terasa premium.',
    },
    {
        value: 'operations',
        label: 'Operations',
        description: 'Tampilan teknis untuk aplikasi internal, dashboard, atau sistem operasional.',
    },
    {
        value: 'minimal',
        label: 'Minimal',
        description: 'Tampilan bersih dan tenang untuk pesan maintenance sederhana.',
    },
];
