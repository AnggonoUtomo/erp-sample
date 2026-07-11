import { Card, CardContent } from '@/components/ui/card';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { ChevronDown, Keyboard } from 'lucide-react';

const shortcuts = [
    { keys: 'Ctrl/Cmd + Shift + A', label: 'Tambah structure' },
    { keys: 'Ctrl/Cmd + K atau /', label: 'Fokus pencarian' },
    { keys: 'Alt + N', label: 'Fokus node type' },
    { keys: 'Alt + S', label: 'Fokus status' },
    { keys: 'Alt + A', label: 'Fokus arsip' },
    { keys: 'Alt + R', label: 'Fokus rows per page' },
    { keys: 'Alt + P', label: 'Fokus pagination' },
    { keys: 'Alt + T', label: 'Fokus row pertama tabel' },
    { keys: 'Enter', label: 'Buka detail row terfokus' },
    { keys: 'Delete', label: 'Arsipkan structure terpilih' },
    { keys: 'Esc', label: 'Keluar dari mode form' },
];

export function OrganizationStructureShortcutPanel() {
    return (
        <Card data-dashboard-card>
            <Collapsible>
                <CollapsibleTrigger className="group flex w-full items-center justify-between gap-3 p-4 text-left">
                    <div className="flex items-center gap-2">
                        <span className="dashboard-icon icon-tone-sky flex size-8 items-center justify-center rounded-lg">
                            <Keyboard className="size-4" />
                        </span>
                        <div>
                            <h2 className="text-sm font-semibold">Keyboard Shortcuts</h2>
                            <p className="text-muted-foreground text-xs">Perintah cepat untuk mengelola Organization Structures.</p>
                        </div>
                    </div>
                    <ChevronDown className="text-muted-foreground size-4 transition-transform group-data-[state=open]:rotate-180" />
                </CollapsibleTrigger>
                <CollapsibleContent>
                    <CardContent className="border-t p-4">
                        <div className="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                            {shortcuts.map((shortcut) => (
                                <div
                                    key={shortcut.keys}
                                    className="bg-background/60 flex items-center justify-between gap-3 rounded-lg border px-3 py-2"
                                >
                                    <span className="text-muted-foreground text-xs">{shortcut.label}</span>
                                    <kbd className="bg-muted text-foreground shrink-0 rounded border px-2 py-1 text-[11px] font-medium">
                                        {shortcut.keys}
                                    </kbd>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </CollapsibleContent>
            </Collapsible>
        </Card>
    );
}
