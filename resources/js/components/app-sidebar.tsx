import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuBadge,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { usePermission } from '@/hooks/use-permission';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    ArchiveRestore,
    BadgeCheck,
    BriefcaseBusiness,
    Building2,
    CalendarClock,
    ChevronRight,
    ClipboardCheck,
    FileSignature,
    Layers,
    LayoutGrid,
    ListChecks,
    ListFilter,
    ListRestart,
    LockKeyhole,
    LogIn,
    LogOut,
    MailCheck,
    MapPin,
    Network,
    Palette,
    ScrollText,
    Settings,
    ShieldCheck,
    SlidersHorizontal,
    UserRound,
    UserRoundCog,
    Users,
    UsersRound,
} from 'lucide-react';
import { type ComponentType, useCallback, useEffect, useRef } from 'react';
import AppLogo from './app-logo';

type SidebarItem = NavItem & { badge?: string; permissions?: string[] };

type SidebarDropdownGroup = {
    title: string;
    icon: SidebarItem['icon'];
    items: SidebarItem[];
};

const SIDEBAR_SCROLL_KEY = 'laravel12-starterkit:sidebar-scroll-top';

const sidebarIconColors: Record<string, string> = {
    Dasbor: '!text-sky-500 dark:!text-sky-400',
    'Manajemen User': '!text-indigo-500 dark:!text-indigo-400',
    'Kontrol Akses': '!text-violet-500 dark:!text-violet-400',
    'System Settings': '!text-purple-500 dark:!text-purple-400',
    'Audit Logs': '!text-orange-500 dark:!text-orange-400',
    'Login Activity': '!text-sky-500 dark:!text-sky-400',
    'Queue Monitor': '!text-sky-500 dark:!text-sky-400',
    'Scheduler Monitor': '!text-orange-500 dark:!text-orange-400',
    'Notification Templates': '!text-emerald-500 dark:!text-emerald-400',
    'Backup & Restore': '!text-amber-500 dark:!text-amber-400',
    Departements: '!text-teal-500 dark:!text-teal-400',
    Positions: '!text-sky-500 dark:!text-sky-400',
    'Job Levels': '!text-cyan-500 dark:!text-cyan-400',
    'Work Locations': '!text-emerald-500 dark:!text-emerald-400',
    'Employment Statuses': '!text-rose-500 dark:!text-rose-400',
    'Employment Types': '!text-pink-500 dark:!text-pink-400',
    'HR Reference Data': '!text-cyan-500 dark:!text-cyan-400',
    'Organization Structures': '!text-emerald-500 dark:!text-emerald-400',
    Employees: '!text-blue-500 dark:!text-blue-400',
    'Employee Contracts': '!text-amber-500 dark:!text-amber-400',
    'Onboarding Templates': '!text-violet-500 dark:!text-violet-400',
    'Employee Onboardings': '!text-emerald-500 dark:!text-emerald-400',
    'Offboarding Templates': '!text-orange-500 dark:!text-orange-400',
    'Employee Offboardings': '!text-rose-500 dark:!text-rose-400',
    'Pengaturan Akun': '!text-purple-500 dark:!text-purple-400',
    Profil: '!text-indigo-500 dark:!text-indigo-400',
    'Kata Sandi': '!text-rose-500 dark:!text-rose-400',
    Tampilan: '!text-amber-500 dark:!text-amber-400',
};

function sidebarIconColor(title: string) {
    return sidebarIconColors[title] ?? '!text-cyan-500 dark:!text-cyan-400';
}

function isItemActive(item: SidebarItem, currentUrl: string) {
    if (item.exact) {
        return currentUrl === item.url;
    }

    if (item.url === '/dashboard') {
        return item.title === 'Dasbor' && (currentUrl === item.url || currentUrl === '/');
    }

    if (item.url === '/hr/dashboard') {
        return currentUrl === item.url;
    }

    return currentUrl.startsWith(item.url);
}

const moduleIconMap: Record<string, ComponentType<{ className?: string }>> = {
    ArchiveRestore,
    BadgeCheck,
    BriefcaseBusiness,
    Building2,
    CalendarClock,
    ClipboardCheck,
    FileSignature,
    Layers,
    ListFilter,
    ListChecks,
    LogIn,
    LogOut,
    ListRestart,
    MailCheck,
    MapPin,
    Network,
    ScrollText,
    ShieldCheck,
    SlidersHorizontal,
    UserRoundCog,
    Users,
    UsersRound,
};

function resolveSidebarIcon(icon: SidebarItem['icon']) {
    if (!icon) {
        return null;
    }

    if (typeof icon === 'string') {
        return moduleIconMap[icon] ?? null;
    }

    return icon;
}

function SidebarItemIcon({ item }: { item: SidebarItem }) {
    const Icon = resolveSidebarIcon(item.icon);

    return Icon ? <Icon className={sidebarIconColor(item.title)} /> : null;
}

const settingsNavItems: SidebarItem[] = [
    {
        title: 'Profil',
        url: '/settings/profile',
        icon: UserRound,
    },
    {
        title: 'Kata Sandi',
        url: '/settings/password',
        icon: LockKeyhole,
    },
    {
        title: 'Tampilan',
        url: '/settings/appearance',
        icon: Palette,
    },
];

function SidebarNavGroup({ title, items }: { title: string; items: SidebarItem[] }) {
    const page = usePage();
    const { canAny } = usePermission();
    const visibleItems = items.filter((item) => !item.permissions || canAny(item.permissions));

    if (!visibleItems.length) {
        return null;
    }

    return (
        <SidebarGroup>
            <SidebarGroupLabel>{title}</SidebarGroupLabel>
            <SidebarGroupContent>
                <SidebarMenu>
                    {visibleItems.map((item) => {
                        const isActive = isItemActive(item, page.url);

                        if (item.children?.length) {
                            return <SidebarDropdownGroup key={`${title}-${item.title}`} title={item.title} icon={item.icon} items={item.children} />;
                        }

                        return (
                            <SidebarMenuItem key={`${title}-${item.title}`}>
                                <SidebarMenuButton asChild isActive={isActive} tooltip={item.title} className={item.badge ? 'pr-9' : undefined}>
                                    <Link href={item.url} prefetch>
                                        <SidebarItemIcon item={item} />
                                        <span>{item.title}</span>
                                    </Link>
                                </SidebarMenuButton>
                                {item.badge && <SidebarMenuBadge className="bg-primary/10 text-primary rounded-full">{item.badge}</SidebarMenuBadge>}
                            </SidebarMenuItem>
                        );
                    })}
                </SidebarMenu>
            </SidebarGroupContent>
        </SidebarGroup>
    );
}

function SidebarDropdownGroup({ title, icon, items }: SidebarDropdownGroup) {
    const page = usePage();
    const { canAny } = usePermission();
    const Icon = resolveSidebarIcon(icon);

    const visibleChildren = items.filter((item) => !item.permissions || canAny(item.permissions));
    const hasActiveChild = visibleChildren.some((item) => isItemActive(item, page.url));

    if (!visibleChildren.length) {
        return null;
    }

    return (
        <Collapsible asChild defaultOpen={hasActiveChild} className="group/collapsible">
            <SidebarMenuItem>
                <CollapsibleTrigger asChild>
                    <SidebarMenuButton isActive={hasActiveChild} tooltip={title}>
                        {Icon && <Icon className={sidebarIconColor(title)} />}
                        <span>{title}</span>
                        <ChevronRight className="text-muted-foreground ml-auto transition-transform group-data-[state=open]/collapsible:rotate-90" />
                    </SidebarMenuButton>
                </CollapsibleTrigger>
                <CollapsibleContent>
                    <SidebarMenuSub>
                        {visibleChildren.map((item) => (
                            <SidebarMenuSubItem key={item.title}>
                                <SidebarMenuSubButton asChild isActive={isItemActive(item, page.url)}>
                                    <Link href={item.url} prefetch>
                                        <SidebarItemIcon item={item} />
                                        <span>{item.title}</span>
                                    </Link>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        ))}
                    </SidebarMenuSub>
                </CollapsibleContent>
            </SidebarMenuItem>
        </Collapsible>
    );
}

function SidebarSettingsDropdown() {
    return (
        <SidebarGroup>
            <SidebarGroupLabel>Akun</SidebarGroupLabel>
            <SidebarGroupContent>
                <SidebarMenu>
                    <SidebarDropdownGroup title="Pengaturan Akun" icon={Settings} items={settingsNavItems} />
                </SidebarMenu>
            </SidebarGroupContent>
        </SidebarGroup>
    );
}

export function AppSidebar() {
    const page = usePage<SharedData>();
    const sidebarContentRef = useRef<HTMLDivElement | null>(null);
    const dashboardUrl = page.url.startsWith('/hr/') ? '/hr/dashboard' : '/dashboard';
    const overviewNavItems: SidebarItem[] = [
        {
            title: 'Dasbor',
            url: dashboardUrl,
            icon: LayoutGrid,
        },
    ];
    const moduleNavGroups = Object.values(
        page.props.navigation.reduce<Record<string, { title: string; items: SidebarItem[] }>>((groups, group) => {
            const title = group.group;

            groups[title] ??= { title, items: [] };
            groups[title].items.push(...(group.items as SidebarItem[]));

            return groups;
        }, {}),
    ).filter((group) => group.items.length > 0);

    const rememberSidebarScroll = useCallback(() => {
        if (typeof window === 'undefined' || !sidebarContentRef.current) {
            return;
        }

        window.sessionStorage.setItem(SIDEBAR_SCROLL_KEY, String(sidebarContentRef.current.scrollTop));
    }, []);

    useEffect(() => {
        if (typeof window === 'undefined') {
            return;
        }

        const savedScrollTop = Number(window.sessionStorage.getItem(SIDEBAR_SCROLL_KEY) ?? '0');
        if (!Number.isFinite(savedScrollTop) || savedScrollTop <= 0) {
            return;
        }

        const restoreScroll = () => {
            if (sidebarContentRef.current) {
                sidebarContentRef.current.scrollTop = savedScrollTop;
            }
        };

        restoreScroll();
        const animationFrame = window.requestAnimationFrame(restoreScroll);
        const timeout = window.setTimeout(restoreScroll, 80);

        return () => {
            window.cancelAnimationFrame(animationFrame);
            window.clearTimeout(timeout);
        };
    }, [page.url]);

    return (
        <Sidebar collapsible="icon" variant="floating">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch onClick={rememberSidebarScroll}>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent ref={sidebarContentRef} className="sidebar-scrollbar-hidden" onScroll={rememberSidebarScroll}>
                <div className="bg-sidebar sticky top-0 z-10 pb-1">
                    <SidebarNavGroup title="Ringkasan" items={overviewNavItems} />
                </div>
                {moduleNavGroups.map((group) => (
                    <SidebarNavGroup key={group.title} title={group.title} items={group.items} />
                ))}
                <div className="mt-auto">
                    <SidebarSettingsDropdown />
                </div>
            </SidebarContent>

            <SidebarFooter />
        </Sidebar>
    );
}
