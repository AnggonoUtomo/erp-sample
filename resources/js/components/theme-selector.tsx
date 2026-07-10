import { themes, useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';
import { Check } from 'lucide-react';

export default function ThemeSelector() {
    const { theme, updateTheme } = useAppearance();

    return (
        <div className="grid gap-3 sm:grid-cols-2">
            {themes.map((item) => {
                const active = theme === item.value;

                return (
                    <button
                        key={item.value}
                        type="button"
                        onClick={() => updateTheme(item.value)}
                        className={cn(
                            'group bg-card hover:border-primary/50 hover:bg-accent/40 flex min-h-28 flex-col justify-between rounded-lg border p-4 text-left shadow-xs transition',
                            active && 'border-primary ring-primary/15 ring-2',
                        )}
                    >
                        <span className="flex items-start justify-between gap-4">
                            <span>
                                <span className="block text-sm font-semibold">{item.label}</span>
                                <span className="text-muted-foreground mt-1 block text-xs leading-5">{item.description}</span>
                            </span>
                            <span
                                className={cn(
                                    'text-primary flex size-6 shrink-0 items-center justify-center rounded-full border opacity-0 transition',
                                    active && 'opacity-100',
                                )}
                            >
                                <Check className="size-3.5" />
                            </span>
                        </span>

                        <span className="mt-4 flex gap-2">
                            {item.colors.map((color) => (
                                <span key={color} className="h-7 flex-1 rounded-md border" style={{ backgroundColor: color }} />
                            ))}
                        </span>
                    </button>
                );
            })}
        </div>
    );
}
