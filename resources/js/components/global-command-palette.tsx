import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { buildNavigationSearchResults, filterNavigationSearchResults, moveCommandPaletteSelection } from '@/lib/navigation-search';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { ArrowRight, Search, Sparkles } from 'lucide-react';
import { type KeyboardEvent, useEffect, useMemo, useRef, useState } from 'react';

function isTypingTarget(target: EventTarget | null) {
    if (!(target instanceof HTMLElement)) {
        return false;
    }

    const tagName = target.tagName.toLowerCase();

    return tagName === 'input' || tagName === 'textarea' || tagName === 'select' || target.isContentEditable;
}

export function GlobalCommandPalette({ className }: { className?: string }) {
    const { auth, navigation } = usePage<SharedData>().props;
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const [selectedIndex, setSelectedIndex] = useState(0);
    const inputRef = useRef<HTMLInputElement>(null);
    const results = useMemo(
        () =>
            buildNavigationSearchResults(navigation, {
                permissions: auth.permissions,
            }),
        [auth.permissions, navigation],
    );
    const visibleResults = useMemo(() => filterNavigationSearchResults(results, query), [query, results]);

    useEffect(() => {
        setSelectedIndex(0);
    }, [query, visibleResults.length]);

    useEffect(() => {
        const openFromShortcut = (event: globalThis.KeyboardEvent) => {
            if (event.key.toLowerCase() !== 'k' || (!event.ctrlKey && !event.metaKey) || isTypingTarget(event.target)) {
                return;
            }

            event.preventDefault();
            setOpen(true);
        };

        window.addEventListener('keydown', openFromShortcut);

        return () => window.removeEventListener('keydown', openFromShortcut);
    }, []);

    useEffect(() => {
        if (!open) {
            setQuery('');
            return;
        }

        window.setTimeout(() => inputRef.current?.focus(), 0);
    }, [open]);

    const visitResult = (url: string) => {
        setOpen(false);
        router.visit(url);
    };

    const handleSearchKeyDown = (event: KeyboardEvent<HTMLInputElement>) => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setSelectedIndex((currentIndex) => moveCommandPaletteSelection(currentIndex, visibleResults.length, 'next'));
            return;
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            setSelectedIndex((currentIndex) => moveCommandPaletteSelection(currentIndex, visibleResults.length, 'previous'));
            return;
        }

        if (event.key === 'Enter' && visibleResults[selectedIndex]) {
            event.preventDefault();
            visitResult(visibleResults[selectedIndex].url);
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            setOpen(false);
        }
    };

    return (
        <>
            <button
                type="button"
                className={cn(
                    'border-sidebar-border/80 bg-sidebar-accent/70 text-muted-foreground hover:bg-accent hover:text-accent-foreground hidden h-10 max-w-xl min-w-44 flex-1 items-center gap-2 rounded-xl border px-3 text-left text-sm shadow-xs transition-colors lg:flex',
                    className,
                )}
                onClick={() => setOpen(true)}
            >
                <Search className="size-4 shrink-0" />
                <span className="truncate">Cari menu, data, laporan...</span>
                <kbd className="bg-sidebar text-muted-foreground ml-auto rounded-md px-1.5 py-0.5 text-[11px]">Ctrl K</kbd>
            </button>

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent className="top-[18%] max-w-2xl translate-y-0 gap-0 overflow-hidden rounded-2xl border p-0 shadow-2xl sm:rounded-2xl">
                    <DialogHeader className="sr-only">
                        <DialogTitle>Command palette</DialogTitle>
                        <DialogDescription>Cari menu dan halaman yang boleh Anda akses.</DialogDescription>
                    </DialogHeader>

                    <div className="border-border/80 flex items-center gap-3 border-b px-4 py-3">
                        <Search className="text-muted-foreground size-5 shrink-0" />
                        <input
                            ref={inputRef}
                            value={query}
                            onChange={(event) => setQuery(event.target.value)}
                            onKeyDown={handleSearchKeyDown}
                            className="placeholder:text-muted-foreground min-w-0 flex-1 bg-transparent text-base outline-none"
                            placeholder="Cari menu atau halaman..."
                            aria-label="Cari menu atau halaman"
                        />
                        <kbd className="bg-muted text-muted-foreground hidden rounded-md px-2 py-1 text-xs sm:inline">Esc</kbd>
                    </div>

                    <div className="bg-muted/30 max-h-[24rem] overflow-y-auto p-2">
                        {visibleResults.length > 0 ? (
                            <div className="space-y-1">
                                {visibleResults.map((result, index) => (
                                    <button
                                        key={result.id}
                                        type="button"
                                        className={cn(
                                            'hover:bg-accent hover:text-accent-foreground focus-visible:ring-ring flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left outline-none focus-visible:ring-2',
                                            index === selectedIndex && 'bg-accent text-accent-foreground',
                                        )}
                                        onClick={() => visitResult(result.url)}
                                        onMouseEnter={() => setSelectedIndex(index)}
                                    >
                                        <div className="bg-primary/10 text-primary flex size-9 shrink-0 items-center justify-center rounded-lg">
                                            <Search className="size-4" />
                                        </div>
                                        <span className="min-w-0 flex-1">
                                            <span className="block truncate text-sm font-medium">{result.title}</span>
                                            <span className="text-muted-foreground mt-0.5 block truncate text-xs">
                                                {result.group} - {result.url}
                                            </span>
                                        </span>
                                        {result.badge && (
                                            <span className="bg-primary/10 text-primary rounded-full px-2 py-0.5 text-[11px]">{result.badge}</span>
                                        )}
                                        <ArrowRight className="text-muted-foreground size-4 shrink-0" />
                                    </button>
                                ))}
                            </div>
                        ) : (
                            <div className="px-2 py-8">
                                <div className="border-border/70 bg-background mx-auto max-w-md rounded-2xl border p-5 text-center shadow-xs">
                                    <div className="bg-primary/10 text-primary mx-auto flex size-11 items-center justify-center rounded-xl">
                                        <Sparkles className="size-5" />
                                    </div>
                                    <h3 className="mt-4 text-sm font-semibold">
                                        {query ? 'Tidak ada menu yang cocok' : 'Cari menu yang boleh Anda akses'}
                                    </h3>
                                    <p className="text-muted-foreground mt-2 text-sm leading-relaxed">
                                        {query
                                            ? 'Coba kata kunci lain, atau pastikan akun Anda memiliki permission untuk menu tersebut.'
                                            : 'Ketik nama menu, kategori, atau halaman. Hasil pencarian mengikuti permission akun Anda.'}
                                    </p>
                                </div>
                            </div>
                        )}
                    </div>
                </DialogContent>
            </Dialog>
        </>
    );
}
