import { useEffect, useState } from 'react';

export type Appearance = 'light' | 'dark';
export type Theme = 'urban' | 'graphite' | 'mist' | 'harbor' | 'quartz' | 'aurora' | 'saffron' | 'ruby' | 'forest' | 'ocean' | 'plum' | 'copper';

export const themes: { value: Theme; label: string; description: string; colors: string[] }[] = [
    {
        value: 'urban',
        label: 'Urban',
        description: 'Biru bersih untuk panel operasional.',
        colors: ['oklch(0.47 0.11 252)', 'oklch(0.57 0.085 193)', 'oklch(0.65 0.12 82)'],
    },
    {
        value: 'graphite',
        label: 'Graphite',
        description: 'Netral baja dengan aksen teal.',
        colors: ['oklch(0.43 0.055 245)', 'oklch(0.48 0.085 185)', 'oklch(0.62 0.09 80)'],
    },
    {
        value: 'mist',
        label: 'Mist',
        description: 'Hijau lembut dengan sentuhan hangat.',
        colors: ['oklch(0.46 0.085 135)', 'oklch(0.62 0.105 76)', 'oklch(0.58 0.09 35)'],
    },
    {
        value: 'harbor',
        label: 'Harbor',
        description: 'Biru tenang dengan kontras jelas.',
        colors: ['oklch(0.49 0.11 245)', 'oklch(0.55 0.085 195)', 'oklch(0.62 0.105 82)'],
    },
    {
        value: 'quartz',
        label: 'Quartz',
        description: 'Netral, rapi, dan tenang.',
        colors: ['oklch(0.21 0.006 285)', 'oklch(0.48 0.006 285)', 'oklch(0.62 0.08 185)'],
    },
    {
        value: 'aurora',
        label: 'Aurora',
        description: 'Teal, indigo, dan lime untuk dashboard analitik.',
        colors: ['oklch(0.45 0.105 190)', 'oklch(0.52 0.12 268)', 'oklch(0.68 0.13 128)'],
    },
    {
        value: 'saffron',
        label: 'Saffron',
        description: 'Emas hangat dengan keseimbangan teal dan rose.',
        colors: ['oklch(0.62 0.13 78)', 'oklch(0.48 0.09 188)', 'oklch(0.58 0.13 18)'],
    },
    {
        value: 'ruby',
        label: 'Ruby',
        description: 'Rose, cyan, dan graphite untuk admin yang lebih tajam.',
        colors: ['oklch(0.52 0.16 18)', 'oklch(0.55 0.095 205)', 'oklch(0.38 0.035 260)'],
    },
    {
        value: 'forest',
        label: 'Forest',
        description: 'Hijau matang dengan aksen lime dan teal.',
        colors: ['oklch(0.43 0.105 145)', 'oklch(0.62 0.13 125)', 'oklch(0.5 0.085 185)'],
    },
    {
        value: 'ocean',
        label: 'Ocean',
        description: 'Navy, cyan, dan mint untuk dashboard yang tenang.',
        colors: ['oklch(0.45 0.12 235)', 'oklch(0.62 0.105 205)', 'oklch(0.58 0.09 165)'],
    },
    {
        value: 'plum',
        label: 'Plum',
        description: 'Ungu elegan dengan sentuhan rose dan slate.',
        colors: ['oklch(0.48 0.13 305)', 'oklch(0.58 0.12 345)', 'oklch(0.44 0.04 255)'],
    },
    {
        value: 'copper',
        label: 'Copper',
        description: 'Tembaga hangat dengan nuansa amber dan olive.',
        colors: ['oklch(0.55 0.12 48)', 'oklch(0.64 0.12 72)', 'oklch(0.48 0.075 120)'],
    },
];

const applyTheme = (appearance: Appearance) => {
    document.documentElement.classList.toggle('dark', appearance === 'dark');
};

const applyColorTheme = (theme: Theme) => {
    document.documentElement.dataset.theme = theme;
};

export function initializeTheme() {
    const savedAppearance = localStorage.getItem('appearance') === 'dark' ? 'dark' : 'light';
    const savedTheme = (localStorage.getItem('theme') as Theme) || 'urban';

    applyTheme(savedAppearance);
    applyColorTheme(savedTheme);
}

export function useAppearance() {
    const [appearance, setAppearance] = useState<Appearance>('light');
    const [theme, setTheme] = useState<Theme>('urban');

    const updateAppearance = (mode: Appearance) => {
        setAppearance(mode);
        localStorage.setItem('appearance', mode);
        applyTheme(mode);
    };

    const updateTheme = (themeName: Theme) => {
        setTheme(themeName);
        localStorage.setItem('theme', themeName);
        applyColorTheme(themeName);
    };

    useEffect(() => {
        const savedAppearance = localStorage.getItem('appearance') === 'dark' ? 'dark' : 'light';
        const savedTheme = localStorage.getItem('theme') as Theme | null;

        updateAppearance(savedAppearance);
        updateTheme(savedTheme || 'urban');
    }, []);

    return { appearance, theme, updateAppearance, updateTheme };
}
