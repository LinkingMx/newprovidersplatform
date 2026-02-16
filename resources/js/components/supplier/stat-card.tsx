import type { LucideIcon } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';

interface StatCardProps {
    icon: LucideIcon;
    value: string | number;
    label: string;
    colorScheme?: 'primary' | 'blue' | 'green' | 'amber';
}

const colorSchemes = {
    primary: 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary',
    blue: 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
    green: 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
    amber: 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
} as const;

export default function StatCard({
    icon: Icon,
    value,
    label,
    colorScheme = 'primary',
}: StatCardProps) {
    return (
        <Card className="py-4">
            <CardContent className="flex flex-col items-center text-center">
                <div
                    className={`flex size-10 items-center justify-center rounded-full ${colorSchemes[colorScheme]}`}
                >
                    <Icon className="size-5" />
                </div>
                <p className="mt-2 text-2xl font-bold text-foreground">
                    {value}
                </p>
                <p className="text-xs text-muted-foreground">{label}</p>
            </CardContent>
        </Card>
    );
}
