import { Head, Link, usePage } from '@inertiajs/react';
import {
    Building2,
    CircleHelp,
    FileText,
    Landmark,
    Mail,
    MapPin,
    Pencil,
    Plus,
    User,
} from 'lucide-react';

import { useState } from 'react';
import BranchRequestDialog from '@/components/supplier/branch-request-dialog';
import BranchRequestList from '@/components/supplier/branch-request-list';
import DocumentRow from '@/components/supplier/document-row';
import ProgressStepper from '@/components/supplier/progress-stepper';
import UploadDialog from '@/components/supplier/upload-dialog';
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
import SupplierLayout from '@/layouts/supplier/supplier-layout';
import type {
    AvailableBranch,
    BranchRequest,
    Supplier,
    SupplierDocument,
} from '@/types/supplier';

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

export default function Dashboard() {
    const { supplier, documents, availableBranches, branchRequests } =
        usePage<{
            supplier: Supplier;
            documents: SupplierDocument[];
            availableBranches: AvailableBranch[];
            branchRequests: BranchRequest[];
        }>().props;
    const config = statusConfig[supplier.status];
    const currentStep = config.step;

    const [uploadDoc, setUploadDoc] = useState<SupplierDocument | null>(null);
    const [showBranchRequest, setShowBranchRequest] = useState(false);

    const canRequestBranch =
        (supplier.status === 'active' ||
            supplier.status === 'profile_completed') &&
        availableBranches.length > 0;

    const approvedCount =
        documents?.filter((d) => d.document_state.color === 'green').length ??
        0;
    const totalDocs = documents?.length ?? 0;

    return (
        <>
            <Head title="Mi Cuenta" />
            <SupplierLayout supplier={supplier}>
                {/* Welcome Section */}
                <div className="mb-8">
                    <div className="flex items-center gap-3">
                        <h1 className="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            Bienvenido, {supplier.name.split(' ')[0]}
                        </h1>
                        <Badge variant={config.variant}>
                            {config.label}
                        </Badge>
                    </div>
                    <p className="mt-1 text-muted-foreground">
                        {config.description}
                    </p>
                </div>

                {/* Progress Stepper */}
                <ProgressStepper
                    currentStep={currentStep}
                    supplierStatus={supplier.status}
                />

                {/* Main Grid */}
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Personal Information */}
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-2">
                                    <User className="size-5 text-primary" />
                                    <CardTitle className="text-base">
                                        Información Personal
                                    </CardTitle>
                                </div>
                                {(supplier.status === 'profile_completed' ||
                                    supplier.status === 'active') && (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        asChild
                                    >
                                        <Link href="/supplier/profile/edit">
                                            <Pencil className="size-3.5" />
                                            Editar
                                        </Link>
                                    </Button>
                                )}
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
                                        <Mail className="size-4 text-muted-foreground" />
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
                                                    <MapPin className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                                                    <p className="text-sm text-foreground">
                                                        {supplier.address_street}
                                                        {supplier.address_number &&
                                                            ` ${supplier.address_number}`}
                                                        {supplier.address_neighborhood && (
                                                            <>
                                                                <br />
                                                                Col.{' '}
                                                                {
                                                                    supplier.address_neighborhood
                                                                }
                                                            </>
                                                        )}
                                                        <br />
                                                        {supplier.address_city}
                                                        {supplier.address_zip &&
                                                            `, C.P. ${supplier.address_zip}`}
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
                                                    <Landmark className="size-4 text-muted-foreground" />
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

                    {/* Branches */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center gap-2">
                                <Building2 className="size-5 text-primary" />
                                <CardTitle className="text-base">
                                    Mis Sucursales
                                </CardTitle>
                                {supplier.branches &&
                                    supplier.branches.length > 0 && (
                                        <Badge variant="secondary">
                                            {supplier.branches.length}
                                        </Badge>
                                    )}
                            </div>
                            {canRequestBranch && (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    className="mt-2 w-full"
                                    onClick={() =>
                                        setShowBranchRequest(true)
                                    }
                                >
                                    <Plus className="size-3.5" />
                                    Solicitar Sucursal
                                </Button>
                            )}
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
                                            <div className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary/20">
                                                <Building2 className="size-4 text-primary" />
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
                                        <Building2 className="size-6 text-muted-foreground" />
                                    </div>
                                    <p className="mt-3 text-sm font-medium text-muted-foreground">
                                        Sin sucursales asignadas
                                    </p>
                                    <p className="mt-1 text-xs text-muted-foreground/70">
                                        Solicita asignación a una sucursal o
                                        espera a que tu administrador lo haga
                                    </p>
                                </div>
                            )}

                            <BranchRequestList
                                branchRequests={branchRequests ?? []}
                            />
                        </CardContent>
                    </Card>
                </div>

                {/* Documents Section */}
                <Card className="mt-6">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-2">
                                <FileText className="size-5 text-primary" />
                                <CardTitle className="text-base">
                                    Mis Documentos
                                </CardTitle>
                            </div>
                            {totalDocs > 0 && (
                                <Badge variant="secondary">
                                    {approvedCount}/{totalDocs}
                                </Badge>
                            )}
                        </div>
                        <CardDescription>
                            Documentos asignados por tu administrador. Sube los
                            archivos requeridos.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {totalDocs > 0 ? (
                            <>
                                {/* Progress Bar */}
                                <div className="mb-4">
                                    <div className="mb-1 flex items-center justify-between text-xs text-muted-foreground">
                                        <span>Progreso de documentos</span>
                                        <span>
                                            {approvedCount} de {totalDocs}{' '}
                                            aprobados
                                        </span>
                                    </div>
                                    <div className="h-2 w-full rounded-full bg-muted">
                                        <div
                                            className="h-2 rounded-full bg-green-500 transition-all duration-300 dark:bg-green-400"
                                            style={{
                                                width: `${(approvedCount / totalDocs) * 100}%`,
                                            }}
                                        />
                                    </div>
                                </div>
                                <div className="space-y-3">
                                    {documents.map((doc) => (
                                        <DocumentRow
                                            key={doc.id}
                                            document={doc}
                                            onUpload={setUploadDoc}
                                        />
                                    ))}
                                </div>
                            </>
                        ) : (
                            <div className="flex flex-col items-center justify-center py-8 text-center">
                                <div className="flex size-12 items-center justify-center rounded-full bg-muted">
                                    <FileText className="size-6 text-muted-foreground" />
                                </div>
                                <p className="mt-3 text-sm font-medium text-muted-foreground">
                                    Sin documentos asignados
                                </p>
                                <p className="mt-1 text-xs text-muted-foreground/70">
                                    Tu administrador asignará los documentos
                                    requeridos
                                </p>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Dialogs */}
                <UploadDialog
                    document={uploadDoc}
                    onClose={() => setUploadDoc(null)}
                />
                <BranchRequestDialog
                    open={showBranchRequest}
                    onClose={() => setShowBranchRequest(false)}
                    availableBranches={availableBranches ?? []}
                />

                {/* Help Section */}
                <Card className="mt-6 border-primary/20 bg-primary/5">
                    <CardContent className="flex flex-col items-start gap-4 pt-6 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-start gap-3">
                            <div className="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary/10">
                                <CircleHelp className="size-5 text-primary" />
                            </div>
                            <div>
                                <p className="font-semibold text-foreground">
                                    ¿Necesitas ayuda?
                                </p>
                                <p className="mt-0.5 text-sm text-muted-foreground">
                                    Nuestro equipo de soporte está listo para
                                    asistirte
                                </p>
                            </div>
                        </div>
                        <Button variant="outline" size="sm" asChild>
                            <a href="mailto:administracion@grupocosteno.com">
                                <Mail className="size-4" />
                                Email
                            </a>
                        </Button>
                    </CardContent>
                </Card>
            </SupplierLayout>
        </>
    );
}
