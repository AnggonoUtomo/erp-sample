import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useIsMobile } from '@/hooks/use-mobile';
import { cn } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { KeyRound, Palette, UserRound } from 'lucide-react';

const sidebarNavItems: Array<NavItem & { description: string }> = [
    {
        title: 'Profil',
        url: '/settings/profile',
        icon: UserRound,
        description: 'Nama, email, dan identitas akun.',
    },
    {
        title: 'Kata Sandi',
        url: '/settings/password',
        icon: KeyRound,
        description: 'Perbarui kredensial login.',
    },
    {
        title: 'Tampilan',
        url: '/settings/appearance',
        icon: Palette,
        description: 'Mode tampilan dan tema aplikasi.',
    },
];

const settingsIconColors: Record<string, string> = {
    Profil: 'text-indigo-500 dark:text-indigo-400',
    'Kata Sandi': 'text-rose-500 dark:text-rose-400',
    Tampilan: 'text-amber-500 dark:text-amber-400',
};

function settingsIconColor(title: string) {
    return settingsIconColors[title] ?? 'text-cyan-500 dark:text-cyan-400';
}

export default function SettingsLayout({ children }: { children: React.ReactNode }) {
    const { url } = usePage();
    const isMobile = useIsMobile();
    const activeItem = sidebarNavItems.find((item) => url.startsWith(item.url)) ?? sidebarNavItems[0];
    const ActiveIcon = activeItem.icon ?? UserRound;

    if (isMobile) {
        return (
            <div className="flex min-h-full flex-col gap-4 px-4 py-4">
                <div className="space-y-3">
                    <div className="flex items-center justify-between gap-3">
                        <div className="min-w-0">
                            <p className="text-muted-foreground text-xs font-medium tracking-wide uppercase">Pengaturan Akun</p>
                            <h1 className="truncate text-xl font-semibold tracking-tight">{activeItem.title}</h1>
                        </div>
                        <span className="flex size-11 shrink-0 items-center justify-center">
                            <ActiveIcon className={cn('size-6', settingsIconColor(activeItem.title))} />
                        </span>
                    </div>

                    <div className="sidebar-scrollbar-hidden -mx-4 flex gap-2 overflow-x-auto px-4 pb-1">
                        {sidebarNavItems.map((item) => {
                            const Icon = item.icon ?? UserRound;
                            const isActive = activeItem.url === item.url;

                            return (
                                <Link
                                    key={item.url}
                                    href={item.url}
                                    prefetch
                                    className={cn(
                                        'flex shrink-0 items-center gap-2 rounded-full border px-3 py-2 text-sm font-medium transition',
                                        isActive
                                            ? 'border-primary/30 bg-primary text-primary-foreground shadow-sm'
                                            : 'bg-background text-muted-foreground hover:bg-muted/70',
                                    )}
                                >
                                    <Icon className={cn('size-4', settingsIconColor(item.title))} />
                                    {item.title}
                                </Link>
                            );
                        })}
                    </div>
                </div>

                <section className="space-y-5 pb-4">{children}</section>
            </div>
        );
    }

    return (
        <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div className="space-y-1">
                    <h1 className="text-2xl font-semibold tracking-tight">Pengaturan</h1>
                    <p className="text-muted-foreground text-sm">Kelola profil, keamanan, dan preferensi tampilan akun.</p>
                </div>
                <Badge variant="outline" className="w-fit">
                    Akun
                </Badge>
            </div>

            <div className="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
                <Card data-dashboard-card className="h-fit overflow-hidden">
                    <CardHeader className="border-b">
                        <CardTitle className="text-base">Menu Pengaturan</CardTitle>
                        <CardDescription>Pilih area pengaturan akun.</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-2 p-3">
                        {sidebarNavItems.map((item) => {
                            const Icon = item.icon ?? UserRound;
                            const isActive = activeItem.url === item.url;

                            return (
                                <Link
                                    key={item.url}
                                    href={item.url}
                                    prefetch
                                    className={cn(
                                        'flex items-start gap-3 rounded-lg border p-3 text-left transition',
                                        isActive
                                            ? 'border-primary/40 bg-primary/10 text-primary'
                                            : 'hover:border-border hover:bg-muted/60 border-transparent',
                                    )}
                                >
                                    <span className="mt-0.5 flex size-9 shrink-0 items-center justify-center">
                                        <Icon className={cn('size-5', settingsIconColor(item.title))} />
                                    </span>
                                    <span className="min-w-0">
                                        <span className="block text-sm font-medium">{item.title}</span>
                                        <span className="text-muted-foreground mt-0.5 block text-xs leading-relaxed">{item.description}</span>
                                    </span>
                                </Link>
                            );
                        })}
                    </CardContent>
                </Card>

                <Card data-dashboard-card className="min-w-0 overflow-hidden">
                    <CardHeader className="border-b">
                        <div className="flex items-start gap-3">
                            <span className="flex size-11 shrink-0 items-center justify-center">
                                <ActiveIcon className={cn('size-6', settingsIconColor(activeItem.title))} />
                            </span>
                            <div className="space-y-1">
                                <CardTitle>{activeItem.title}</CardTitle>
                                <CardDescription>{activeItem.description}</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="p-5 sm:p-6">
                        <section className="max-w-2xl space-y-10">{children}</section>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}
