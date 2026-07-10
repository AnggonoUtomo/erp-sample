import { retryUnitOptions } from './options';
import type { HealthStatus, RetryUnit } from './types';

export function boolLabel(value: boolean) {
    return value ? 'Aktif' : 'Nonaktif';
}

export function healthStatusLabel(status: HealthStatus) {
    return status === 'ok' ? 'Healthy' : status === 'warning' ? 'Warning' : 'Error';
}

export function healthBadgeVariant(status: HealthStatus) {
    return status === 'ok' ? 'default' : status === 'warning' ? 'secondary' : 'destructive';
}

export function healthIconClass(status: HealthStatus) {
    return status === 'ok'
        ? 'text-emerald-600 dark:text-emerald-400'
        : status === 'warning'
          ? 'text-amber-600 dark:text-amber-400'
          : 'text-rose-600 dark:text-rose-400';
}

export function retrySecondsToParts(seconds: number | null): { amount: string; unit: RetryUnit } {
    if (!seconds) {
        return { amount: '', unit: 'minutes' };
    }

    for (const option of retryUnitOptions) {
        if (seconds >= option.multiplier && seconds % option.multiplier === 0) {
            return {
                amount: String(seconds / option.multiplier),
                unit: option.value,
            };
        }
    }

    return { amount: String(seconds), unit: 'seconds' };
}

export function retryPartsToSeconds(amount: string, unit: RetryUnit) {
    if (!amount) {
        return '';
    }

    const multiplier = retryUnitOptions.find((option) => option.value === unit)?.multiplier ?? 1;
    const numericAmount = Number(amount);

    if (!Number.isFinite(numericAmount)) {
        return '';
    }

    return String(Math.floor(numericAmount * multiplier));
}

export function formatRetryDuration(seconds: number | null) {
    const parts = retrySecondsToParts(seconds);

    if (!parts.amount) {
        return 'Off';
    }

    const label = retryUnitOptions.find((option) => option.value === parts.unit)?.label.toLowerCase() ?? 'detik';

    return `${parts.amount} ${label}`;
}

export function formatSecondsBreakdown(seconds: number | null) {
    if (!seconds || seconds < 1) {
        return null;
    }

    const days = Math.floor(seconds / 86400);
    const hours = Math.floor((seconds % 86400) / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = seconds % 60;

    const parts = [
        days ? `${days} hari` : null,
        hours ? `${hours} jam` : null,
        minutes ? `${minutes} menit` : null,
        remainingSeconds ? `${remainingSeconds} detik` : null,
    ].filter(Boolean);

    return parts.length ? parts.join(' ') : `${seconds} detik`;
}

export function filePreview(file: File | null, fallback: string | null, removed: boolean) {
    if (removed) {
        return null;
    }

    if (file) {
        return URL.createObjectURL(file);
    }

    return fallback;
}
