import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { ChevronDown, Keyboard } from 'lucide-react';

export function EmployeeShortcutPanel() {
    const shortcuts = [
        ['Ctrl/Cmd + Shift + A', 'Tambah employee'],
        ['Ctrl/Cmd + K atau /', 'Fokus pencarian'],
        ['Alt + D', 'Fokus filter departement'],
        ['Alt + S', 'Fokus filter status'],
        ['Alt + A', 'Fokus arsip'],
        ['Alt + R', 'Fokus jumlah row'],
        ['Alt + P', 'Fokus pagination'],
        ['Alt + T', 'Fokus row pertama'],
        ['Delete', 'Arsipkan employee terpilih'],
        ['Esc', 'Tutup form'],
    ];

    return (
        <div className="bg-card rounded-lg border">
            <Collapsible>
                <CollapsibleTrigger className="group flex w-full items-center justify-between gap-3 p-4 text-left">
                    <span className="flex items-center gap-3">
                        <span className="dashboard-icon icon-tone-sky flex size-9 items-center justify-center rounded-lg">
                            <Keyboard className="size-4" />
                        </span>
                        <span>
                            <span className="block font-medium">Shortcut Keyboard</span>
                            <span className="text-muted-foreground text-sm">Buka untuk melihat pintasan modul Employees.</span>
                        </span>
                    </span>
                    <ChevronDown className="text-muted-foreground size-4 transition-transform group-data-[state=open]:rotate-180" />
                </CollapsibleTrigger>
                <CollapsibleContent>
                    <div className="grid gap-2 border-t p-4 sm:grid-cols-2 lg:grid-cols-3">
                        {shortcuts.map(([key, label]) => (
                            <div key={key} className="bg-muted/30 rounded-md border p-3">
                                <kbd className="text-xs font-semibold">{key}</kbd>
                                <p className="text-muted-foreground mt-1 text-sm">{label}</p>
                            </div>
                        ))}
                    </div>
                </CollapsibleContent>
            </Collapsible>
        </div>
    );
}
