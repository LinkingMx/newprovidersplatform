import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import BrandLogo from '@/components/brand-logo';
import { Button } from '@/components/ui/button';

interface BackLink {
    href: string;
    label: string;
}

interface SupplierAuthLayoutProps {
    title?: string;
    description?: string;
    maxWidth?: 'sm' | 'md' | 'lg';
    backLink?: BackLink;
}

const maxWidthClasses = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-2xl',
} as const;

export default function SupplierAuthLayout({
    children,
    maxWidth = 'md',
    backLink,
}: PropsWithChildren<SupplierAuthLayoutProps>) {
    return (
        <div className="flex min-h-screen flex-col items-center justify-center bg-background p-6 lg:p-8">
            <div className={`w-full ${maxWidthClasses[maxWidth]} space-y-6`}>
                <div className="flex justify-center">
                    <BrandLogo className="h-12" />
                </div>

                {children}

                {backLink && (
                    <div className="text-center">
                        <Button asChild variant="link" size="sm">
                            <Link href={backLink.href}>
                                &larr; {backLink.label}
                            </Link>
                        </Button>
                    </div>
                )}
            </div>
        </div>
    );
}
