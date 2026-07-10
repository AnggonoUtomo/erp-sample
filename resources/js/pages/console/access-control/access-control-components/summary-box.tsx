interface SummaryBoxProps {
    label: string;
    value: string | number;
    tone?: string;
}

export function SummaryBox({ label, value, tone = 'bg-muted/40 text-foreground' }: SummaryBoxProps) {
    return (
        <div className={`rounded-lg border p-3 ${tone}`}>
            <p className="text-muted-foreground text-[11px]">{label}</p>
            <p className="mt-1 text-xl font-bold">{value}</p>
        </div>
    );
}
