import { Head, Link, usePage } from '@inertiajs/react';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';

interface Branch {
    id: number;
    name: string;
    created_at: string;
}

interface Supplier {
    id: number;
    name: string;
    email: string;
    status:
        | 'created'
        | 'invited'
        | 'registered'
        | 'profile_completed'
        | 'active';
    address_street: string | null;
    address_city: string | null;
    address_state: string | null;
    address_zip: string | null;
    address_country: string | null;
    clabe_interbancaria: string | null;
    branches: Branch[];
    created_at: string;
}

const statusConfig = {
    created: {
        label: 'Creado',
        description:
            'Tu cuenta ha sido creada. Revisa tu email para establecer tu contraseña.',
        variant: 'secondary' as const,
        step: 0,
    },
    invited: {
        label: 'Invitado',
        description:
            'Se ha enviado una invitación a tu correo. Establece tu contraseña para continuar.',
        variant: 'secondary' as const,
        step: 0,
    },
    registered: {
        label: 'Registrado',
        description:
            'Completa tu perfil con tu dirección y datos bancarios para activar tu cuenta.',
        variant: 'outline' as const,
        step: 1,
    },
    profile_completed: {
        label: 'En Verificación',
        description:
            'Estamos revisando tu información. Te notificaremos cuando tu cuenta esté activa.',
        variant: 'outline' as const,
        step: 2,
    },
    active: {
        label: 'Activo',
        description:
            '¡Tu cuenta está activa! Ya puedes gestionar tus documentos y sucursales.',
        variant: 'default' as const,
        step: 3,
    },
};

const steps = [
    { label: 'Registro', icon: UserIcon },
    { label: 'Perfil', icon: ClipboardIcon },
    { label: 'Verificación', icon: ShieldIcon },
    { label: 'Activo', icon: CheckCircleIcon },
];

function getInitials(name: string): string {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
}

function maskClabe(clabe: string | null): string {
    if (!clabe) return '';
    return '•••• •••• •••• •••• ' + clabe.slice(-2);
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('es-MX', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

// Icons as inline components
function UserIcon({ className }: { className?: string }) {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            strokeWidth={1.5}
            stroke="currentColor"
            className={className}
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
            />
        </svg>
    );
}

function ClipboardIcon({ className }: { className?: string }) {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            strokeWidth={1.5}
            stroke="currentColor"
            className={className}
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"
            />
        </svg>
    );
}

function ShieldIcon({ className }: { className?: string }) {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            strokeWidth={1.5}
            stroke="currentColor"
            className={className}
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"
            />
        </svg>
    );
}

function CheckCircleIcon({ className }: { className?: string }) {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            strokeWidth={1.5}
            stroke="currentColor"
            className={className}
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
            />
        </svg>
    );
}

function MapPinIcon({ className }: { className?: string }) {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            strokeWidth={1.5}
            stroke="currentColor"
            className={className}
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
            />
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"
            />
        </svg>
    );
}

function BankIcon({ className }: { className?: string }) {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            strokeWidth={1.5}
            stroke="currentColor"
            className={className}
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"
            />
        </svg>
    );
}

function BuildingIcon({ className }: { className?: string }) {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            strokeWidth={1.5}
            stroke="currentColor"
            className={className}
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"
            />
        </svg>
    );
}

function EnvelopeIcon({ className }: { className?: string }) {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            strokeWidth={1.5}
            stroke="currentColor"
            className={className}
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"
            />
        </svg>
    );
}

function LogOutIcon({ className }: { className?: string }) {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            strokeWidth={1.5}
            stroke="currentColor"
            className={className}
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15"
            />
        </svg>
    );
}

function HeadphonesIcon({ className }: { className?: string }) {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            strokeWidth={1.5}
            stroke="currentColor"
            className={className}
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"
            />
        </svg>
    );
}

export default function Dashboard() {
    const { supplier } = usePage<{ supplier: Supplier }>().props;
    const config = statusConfig[supplier.status];
    const currentStep = config.step;

    return (
        <>
            <Head title="Mi Cuenta" />
            <div className="min-h-screen bg-background">
                {/* Top Navigation Bar */}
                <header className="sticky top-0 z-10 border-b bg-card/80 backdrop-blur-sm">
                    <div className="mx-auto flex max-w-5xl items-center justify-between px-4 py-3 sm:px-6">
                        <div className="flex items-center gap-3">
                            <Avatar className="size-10 border-2 border-primary/20">
                                <AvatarFallback className="bg-primary/10 text-sm font-semibold text-primary">
                                    {getInitials(supplier.name)}
                                </AvatarFallback>
                            </Avatar>
                            <div className="hidden sm:block">
                                <p className="text-sm leading-none font-semibold text-foreground">
                                    {supplier.name}
                                </p>
                                <p className="mt-0.5 text-xs text-muted-foreground">
                                    {supplier.email}
                                </p>
                            </div>
                        </div>
                        <div className="flex items-center gap-3">
                            <Badge variant={config.variant}>
                                {config.label}
                            </Badge>
                            <Button variant="ghost" size="sm" asChild>
                                <Link
                                    href="/supplier/auth/logout"
                                    method="post"
                                    as="button"
                                >
                                    <LogOutIcon className="size-4" />
                                    <span className="hidden sm:inline">
                                        Cerrar Sesión
                                    </span>
                                </Link>
                            </Button>
                        </div>
                    </div>
                </header>

                {/* Main Content */}
                <main className="mx-auto max-w-5xl px-4 py-8 sm:px-6">
                    {/* Welcome Section */}
                    <div className="mb-8">
                        <h1 className="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            Bienvenido, {supplier.name.split(' ')[0]}
                        </h1>
                        <p className="mt-1 text-muted-foreground">
                            {config.description}
                        </p>
                    </div>

                    {/* Progress Stepper */}
                    <Card className="mb-8">
                        <CardHeader>
                            <CardTitle className="text-base">
                                Progreso de Activación
                            </CardTitle>
                            <CardDescription>
                                Completa todos los pasos para activar tu cuenta
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center justify-between">
                                {steps.map((step, index) => {
                                    const Icon = step.icon;
                                    const isCompleted = index < currentStep;
                                    const isCurrent = index === currentStep;
                                    const isActive =
                                        supplier.status === 'active';

                                    return (
                                        <div
                                            key={step.label}
                                            className="flex flex-1 items-center"
                                        >
                                            <div className="flex flex-col items-center gap-2">
                                                <div
                                                    className={`flex size-10 items-center justify-center rounded-full border-2 transition-colors sm:size-12 ${
                                                        isCompleted ||
                                                        (isActive &&
                                                            index === 3)
                                                            ? 'border-primary bg-primary text-primary-foreground'
                                                            : isCurrent
                                                              ? 'border-primary bg-primary/10 text-primary'
                                                              : 'border-muted bg-muted text-muted-foreground'
                                                    }`}
                                                >
                                                    <Icon className="size-4 sm:size-5" />
                                                </div>
                                                <span
                                                    className={`text-center text-[10px] font-medium sm:text-xs ${
                                                        isCompleted ||
                                                        isCurrent ||
                                                        (isActive &&
                                                            index === 3)
                                                            ? 'text-foreground'
                                                            : 'text-muted-foreground'
                                                    }`}
                                                >
                                                    {step.label}
                                                </span>
                                            </div>
                                            {index < steps.length - 1 && (
                                                <div
                                                    className={`mx-2 h-0.5 flex-1 rounded-full sm:mx-4 ${
                                                        isCompleted
                                                            ? 'bg-primary'
                                                            : 'bg-muted'
                                                    }`}
                                                />
                                            )}
                                        </div>
                                    );
                                })}
                            </div>

                            {/* CTA for incomplete profiles */}
                            {supplier.status === 'registered' && (
                                <div className="mt-6 flex justify-center">
                                    <Button asChild size="lg">
                                        <Link href="/supplier/onboarding">
                                            <ClipboardIcon className="size-4" />
                                            Completar mi Perfil
                                        </Link>
                                    </Button>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Stats Grid */}
                    <div className="mb-8 grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <Card className="py-4">
                            <CardContent className="flex flex-col items-center text-center">
                                <div className="flex size-10 items-center justify-center rounded-full bg-primary/10">
                                    <BuildingIcon className="size-5 text-primary" />
                                </div>
                                <p className="mt-2 text-2xl font-bold text-foreground">
                                    {supplier.branches?.length ?? 0}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    Sucursales
                                </p>
                            </CardContent>
                        </Card>
                        <Card className="py-4">
                            <CardContent className="flex flex-col items-center text-center">
                                <div className="flex size-10 items-center justify-center rounded-full bg-primary/10">
                                    <ShieldIcon className="size-5 text-primary" />
                                </div>
                                <p className="mt-2 text-2xl font-bold text-foreground">
                                    {supplier.status === 'active'
                                        ? '100%'
                                        : currentStep === 2
                                          ? '67%'
                                          : currentStep === 1
                                            ? '33%'
                                            : '0%'}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    Perfil
                                </p>
                            </CardContent>
                        </Card>
                        <Card className="py-4">
                            <CardContent className="flex flex-col items-center text-center">
                                <div className="flex size-10 items-center justify-center rounded-full bg-primary/10">
                                    <MapPinIcon className="size-5 text-primary" />
                                </div>
                                <p className="mt-2 text-2xl font-bold text-foreground">
                                    {supplier.address_city
                                        ? (supplier.address_state ?? '—')
                                        : '—'}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    Ubicación
                                </p>
                            </CardContent>
                        </Card>
                        <Card className="py-4">
                            <CardContent className="flex flex-col items-center text-center">
                                <div className="flex size-10 items-center justify-center rounded-full bg-primary/10">
                                    <BankIcon className="size-5 text-primary" />
                                </div>
                                <p className="mt-2 text-2xl font-bold text-foreground">
                                    {supplier.clabe_interbancaria
                                        ? 'Registrada'
                                        : '—'}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    CLABE
                                </p>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Main Grid */}
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        {/* Personal Information - Takes 2 cols */}
                        <Card className="lg:col-span-2">
                            <CardHeader>
                                <div className="flex items-center gap-2">
                                    <UserIcon className="size-5 text-primary" />
                                    <CardTitle className="text-base">
                                        Información Personal
                                    </CardTitle>
                                </div>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div className="space-y-1">
                                        <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                            Nombre completo
                                        </p>
                                        <p className="font-medium text-foreground">
                                            {supplier.name}
                                        </p>
                                    </div>
                                    <div className="space-y-1">
                                        <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                            Correo electrónico
                                        </p>
                                        <div className="flex items-center gap-2">
                                            <EnvelopeIcon className="size-4 text-muted-foreground" />
                                            <p className="font-medium text-foreground">
                                                {supplier.email}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {(supplier.address_street ||
                                    supplier.clabe_interbancaria) && (
                                    <>
                                        <Separator />
                                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            {supplier.address_street && (
                                                <div className="space-y-1">
                                                    <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                                        Dirección
                                                    </p>
                                                    <div className="flex items-start gap-2">
                                                        <MapPinIcon className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                                                        <p className="text-sm text-foreground">
                                                            {
                                                                supplier.address_street
                                                            }
                                                            <br />
                                                            {
                                                                supplier.address_city
                                                            }
                                                            {supplier.address_state &&
                                                                `, ${supplier.address_state}`}
                                                            {supplier.address_zip &&
                                                                ` ${supplier.address_zip}`}
                                                            {supplier.address_country && (
                                                                <>
                                                                    <br />
                                                                    {
                                                                        supplier.address_country
                                                                    }
                                                                </>
                                                            )}
                                                        </p>
                                                    </div>
                                                </div>
                                            )}
                                            {supplier.clabe_interbancaria && (
                                                <div className="space-y-1">
                                                    <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                                        CLABE Interbancaria
                                                    </p>
                                                    <div className="flex items-center gap-2">
                                                        <BankIcon className="size-4 text-muted-foreground" />
                                                        <p className="font-mono text-sm text-foreground">
                                                            {maskClabe(
                                                                supplier.clabe_interbancaria,
                                                            )}
                                                        </p>
                                                    </div>
                                                </div>
                                            )}
                                        </div>
                                    </>
                                )}

                                <Separator />
                                <div>
                                    <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                        Miembro desde
                                    </p>
                                    <p className="mt-1 text-sm text-foreground">
                                        {formatDate(supplier.created_at)}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Branches - Takes 1 col */}
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <BuildingIcon className="size-5 text-primary" />
                                        <CardTitle className="text-base">
                                            Mis Sucursales
                                        </CardTitle>
                                    </div>
                                    {supplier.branches &&
                                        supplier.branches.length > 0 && (
                                            <Badge variant="secondary">
                                                {supplier.branches.length}
                                            </Badge>
                                        )}
                                </div>
                            </CardHeader>
                            <CardContent>
                                {supplier.branches &&
                                supplier.branches.length > 0 ? (
                                    <div className="space-y-3">
                                        {supplier.branches.map((branch) => (
                                            <div
                                                key={branch.id}
                                                className="flex items-center gap-3 rounded-lg border bg-accent/30 p-3 transition-colors hover:bg-accent/50"
                                            >
                                                <div className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10">
                                                    <BuildingIcon className="size-4 text-primary" />
                                                </div>
                                                <div className="min-w-0 flex-1">
                                                    <p className="truncate text-sm font-medium text-foreground">
                                                        {branch.name}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground">
                                                        Desde{' '}
                                                        {formatDate(
                                                            branch.created_at,
                                                        )}
                                                    </p>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="flex flex-col items-center justify-center py-8 text-center">
                                        <div className="flex size-12 items-center justify-center rounded-full bg-muted">
                                            <BuildingIcon className="size-6 text-muted-foreground" />
                                        </div>
                                        <p className="mt-3 text-sm font-medium text-muted-foreground">
                                            Sin sucursales asignadas
                                        </p>
                                        <p className="mt-1 text-xs text-muted-foreground/70">
                                            Tu administrador asignará sucursales
                                            a tu cuenta
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Help Section */}
                    <Card className="mt-6 border-primary/20 bg-primary/5">
                        <CardContent className="flex flex-col items-start gap-4 pt-6 sm:flex-row sm:items-center sm:justify-between">
                            <div className="flex items-start gap-3">
                                <div className="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary/10">
                                    <HeadphonesIcon className="size-5 text-primary" />
                                </div>
                                <div>
                                    <p className="font-semibold text-foreground">
                                        ¿Necesitas ayuda?
                                    </p>
                                    <p className="mt-0.5 text-sm text-muted-foreground">
                                        Nuestro equipo de soporte está listo
                                        para asistirte
                                    </p>
                                </div>
                            </div>
                            <div className="flex w-full gap-2 sm:w-auto">
                                <Button variant="outline" size="sm" asChild>
                                    <a href="mailto:soporte@ejemplo.com">
                                        <EnvelopeIcon className="size-4" />
                                        Email
                                    </a>
                                </Button>
                                <Button variant="outline" size="sm" asChild>
                                    <a
                                        href="https://wa.me/521234567890"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        WhatsApp
                                    </a>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Footer */}
                    <p className="mt-8 text-center text-xs text-muted-foreground">
                        &copy; {new Date().getFullYear()} Portal de Proveedores.
                        Todos los derechos reservados.
                    </p>
                </main>
            </div>
        </>
    );
}
