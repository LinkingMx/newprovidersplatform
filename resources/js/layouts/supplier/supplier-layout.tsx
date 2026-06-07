import { Link } from '@inertiajs/react';
import { LogOut } from 'lucide-react';
import type { PropsWithChildren } from 'react';
import AppearanceToggleTab from '@/components/appearance-tabs';
import BrandLogo from '@/components/brand-logo';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { Supplier } from '@/types/supplier';

interface SupplierLayoutProps {
    supplier: Supplier;
}

function getInitials(name: string): string {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
}

export default function SupplierLayout({
    children,
    supplier,
}: PropsWithChildren<SupplierLayoutProps>) {
    return (
        <div className="min-h-screen bg-background">
            {/* Sticky Header */}
            <header className="sticky top-0 z-10 border-b bg-card/80 backdrop-blur-sm">
                <div className="mx-auto flex max-w-5xl items-center justify-between px-4 py-3 sm:px-6">
                    <BrandLogo className="h-8" />

                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button
                                variant="ghost"
                                className="size-10 rounded-full p-1"
                            >
                                <Avatar className="size-8 border-2 border-primary/20">
                                    <AvatarFallback className="bg-primary/10 text-xs font-semibold text-primary">
                                        {getInitials(supplier.name)}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent className="w-64" align="end">
                            <DropdownMenuLabel className="p-0 font-normal">
                                <div className="flex items-center gap-3 px-3 py-2">
                                    <Avatar className="size-10 border-2 border-primary/20">
                                        <AvatarFallback className="bg-primary/10 text-sm font-semibold text-primary">
                                            {getInitials(supplier.name)}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div className="min-w-0 flex-1">
                                        <p className="truncate text-sm font-semibold text-foreground">
                                            {supplier.name}
                                        </p>
                                        <p className="truncate text-xs text-muted-foreground">
                                            {supplier.email}
                                        </p>
                                    </div>
                                </div>
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <div className="px-3 py-2">
                                <span className="mb-1.5 block text-xs text-muted-foreground">
                                    Apariencia
                                </span>
                                <AppearanceToggleTab
                                    className="w-full"
                                    iconOnly
                                />
                            </div>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem asChild>
                                <Link
                                    className="block w-full cursor-pointer"
                                    href="/supplier/auth/logout"
                                    method="post"
                                    as="button"
                                >
                                    <LogOut className="mr-2 size-4" />
                                    Cerrar Sesión
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </header>

            {/* Main Content */}
            <main className="mx-auto max-w-5xl px-4 py-8 sm:px-6">
                {children}
            </main>

            {/* Footer */}
            <footer className="pb-8">
                <p className="text-center text-xs text-muted-foreground">
                    &copy; {new Date().getFullYear()} Portal de Proveedores.
                    Todos los derechos reservados.
                </p>
            </footer>
        </div>
    );
}
