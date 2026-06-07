import { Clock, CheckCircle2, XCircle } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import type { BranchRequest } from '@/types/supplier';

interface BranchRequestListProps {
    branchRequests: BranchRequest[];
}

const statusIcons = {
    pending: Clock,
    approved: CheckCircle2,
    rejected: XCircle,
};

const statusVariants: Record<string, 'secondary' | 'default' | 'destructive'> =
    {
        pending: 'secondary',
        approved: 'default',
        rejected: 'destructive',
    };

function formatDate(dateString: string | null): string {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('es-MX', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

export default function BranchRequestList({
    branchRequests,
}: BranchRequestListProps) {
    if (branchRequests.length === 0) return null;

    return (
        <div className="mt-4 space-y-2">
            <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                Mis Solicitudes
            </p>
            <div className="space-y-2">
                {branchRequests.map((request) => {
                    const Icon = statusIcons[request.status] ?? Clock;
                    return (
                        <div
                            key={request.id}
                            className="rounded-lg border bg-background p-3"
                        >
                            <div className="flex items-center justify-between gap-2">
                                <div className="flex min-w-0 items-center gap-2">
                                    <Icon className="size-4 shrink-0 text-muted-foreground" />
                                    <span className="truncate text-sm font-medium">
                                        {request.branch.name}
                                    </span>
                                </div>
                                <Badge
                                    variant={
                                        statusVariants[request.status] ??
                                        'secondary'
                                    }
                                    className="shrink-0"
                                >
                                    {request.status_label}
                                </Badge>
                            </div>
                            {request.status === 'rejected' &&
                                request.notas_admin && (
                                    <p className="mt-2 border-l-2 border-red-300 pl-2 text-xs text-muted-foreground dark:border-red-700">
                                        {request.notas_admin}
                                    </p>
                                )}
                            <p className="mt-1 text-xs text-muted-foreground">
                                {formatDate(request.requested_at)}
                            </p>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
