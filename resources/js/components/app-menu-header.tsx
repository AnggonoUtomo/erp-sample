import { ActivityCenterDropdown } from '@/components/activity-center-dropdown';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { GlobalCommandPalette } from '@/components/global-command-palette';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { UserMenuContent } from '@/components/user-menu-content';
import { useAppearance } from '@/hooks/use-appearance';
import { useInitials } from '@/hooks/use-initials';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem as BreadcrumbItemType, type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { HelpCircle, Moon, Sun } from 'lucide-react';
import { useEffect, useState } from 'react';

export function AppMenuHeader({ breadcrumbs = [] }: { breadcrumbs?: BreadcrumbItemType[] }) {
    const { auth } = usePage<SharedData>().props;
    const { appearance, updateAppearance } = useAppearance();
    const getInitials = useInitials();
    const isDark = appearance === 'dark';
    const [isSticky, setIsSticky] = useState(false);

    useEffect(() => {
        const updateStickyState = () => setIsSticky(window.scrollY > 4);

        updateStickyState();
        window.addEventListener('scroll', updateStickyState, { passive: true });

        return () => window.removeEventListener('scroll', updateStickyState);
    }, []);

    if (!auth.user) {
        return null;
    }

    return (
        <header
            className={cn(
                'border-sidebar-border/70 bg-sidebar text-sidebar-foreground sticky top-0 z-40 border-b px-3 py-2 transition-all duration-200 md:px-5',
                isSticky && 'supports-[backdrop-filter]:bg-sidebar/78 bg-sidebar/92 shadow-sm backdrop-blur-xl',
            )}
        >
            <div className="flex min-h-12 items-center justify-between gap-3">
                <div className="flex min-w-0 flex-1 items-center gap-2">
                    <SidebarTrigger className="size-9 rounded-lg" />
                    <div className="bg-border hidden h-5 w-px sm:block" />
                    <div className="min-w-0 shrink">
                        <Breadcrumbs breadcrumbs={breadcrumbs} />
                    </div>

                    <GlobalCommandPalette className="ml-auto" />
                </div>

                <div className="flex items-center gap-1.5 sm:gap-2">
                    <ActivityCenterDropdown />

                    <Button type="button" variant="ghost" size="icon" className="size-9 rounded-lg">
                        <HelpCircle className="size-4" />
                        <span className="sr-only">Bantuan</span>
                    </Button>

                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        className="size-9 rounded-lg"
                        onClick={() => updateAppearance(isDark ? 'light' : 'dark')}
                    >
                        {isDark ? <Moon className="size-4" /> : <Sun className="size-4" />}
                        <span className="sr-only">Toggle dark mode</span>
                    </Button>

                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost" className="h-9 rounded-lg px-1.5">
                                <Avatar className="size-7 overflow-hidden rounded-md">
                                    <AvatarImage src={auth.user.avatar} alt={auth.user.name} />
                                    <AvatarFallback className="bg-primary/10 text-primary rounded-md text-xs font-semibold">
                                        {getInitials(auth.user.name)}
                                    </AvatarFallback>
                                </Avatar>
                                <span className="hidden max-w-32 truncate text-sm font-medium sm:inline">{auth.user.name}</span>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent className="w-56 rounded-lg" align="end">
                            <UserMenuContent user={auth.user} />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </header>
    );
}
