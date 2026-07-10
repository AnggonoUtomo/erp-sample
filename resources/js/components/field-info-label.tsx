import { Label } from '@/components/ui/label';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { cn } from '@/lib/utils';
import { CircleHelp } from 'lucide-react';
import type { ComponentPropsWithoutRef } from 'react';

type FieldInfoLabelProps = ComponentPropsWithoutRef<typeof Label> & {
    required?: boolean;
    description?: string;
};

export function FieldInfoLabel({ children, className, required, description, ...props }: FieldInfoLabelProps) {
    return (
        <div className="flex items-center gap-1.5">
            <Label className={cn('inline-flex items-center gap-1', className)} {...props}>
                <span>{children}</span>
                {required && (
                    <span aria-label="required" className="text-destructive">
                        *
                    </span>
                )}
            </Label>
            {description && (
                <TooltipProvider delayDuration={150}>
                    <Tooltip>
                        <TooltipTrigger type="button" className="text-muted-foreground hover:text-foreground inline-flex transition">
                            <CircleHelp className="size-3.5" />
                            <span className="sr-only">Deskripsi field</span>
                        </TooltipTrigger>
                        <TooltipContent className="max-w-64 text-xs">{description}</TooltipContent>
                    </Tooltip>
                </TooltipProvider>
            )}
        </div>
    );
}
