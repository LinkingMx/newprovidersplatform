import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

export default function Welcome() {
    return (
        <>
            <Head title="Bienvenido" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-background p-6 lg:p-8">
                <div className="w-full max-w-2xl space-y-8">
                    {/* Header */}
                    <div className="space-y-2 text-center">
                        <h1 className="text-3xl font-bold tracking-tight text-foreground lg:text-4xl">
                            Portal de Proveedores
                        </h1>
                        <p className="text-base text-muted-foreground lg:text-lg">
                            Bienvenido al sistema de gesti&oacute;n de
                            proveedores.
                            <br />
                            Selecciona tu tipo de acceso para continuar.
                        </p>
                    </div>

                    {/* Cards */}
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                        {/* Supplier Card */}
                        <Card className="relative transition-shadow hover:shadow-md">
                            <CardHeader>
                                <div className="flex size-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        strokeWidth={1.5}
                                        stroke="currentColor"
                                        className="size-6"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"
                                        />
                                    </svg>
                                </div>
                                <CardTitle className="text-lg">
                                    Soy Proveedor
                                </CardTitle>
                                <CardDescription>
                                    Accede al portal para completar tu registro,
                                    cargar documentos y gestionar tu cuenta de
                                    proveedor.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Button asChild className="w-full">
                                    <Link href="/supplier/login">
                                        Ingresar como Proveedor
                                    </Link>
                                </Button>
                            </CardContent>
                        </Card>

                        {/* Admin Card */}
                        <Card className="relative transition-shadow hover:shadow-md">
                            <CardHeader>
                                <div className="flex size-12 items-center justify-center rounded-lg bg-secondary text-secondary-foreground">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        strokeWidth={1.5}
                                        stroke="currentColor"
                                        className="size-6"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"
                                        />
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                        />
                                    </svg>
                                </div>
                                <CardTitle className="text-lg">
                                    Soy Administrador
                                </CardTitle>
                                <CardDescription>
                                    Accede al panel de administraci&oacute;n
                                    para gestionar proveedores, documentos y
                                    configuraci&oacute;n del sistema.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Button
                                    asChild
                                    variant="outline"
                                    className="w-full"
                                >
                                    <a href="/admin/login">
                                        Ingresar como Administrador
                                    </a>
                                </Button>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Footer */}
                    <p className="text-center text-xs text-muted-foreground">
                        &copy; {new Date().getFullYear()} Portal de Proveedores.
                        Todos los derechos reservados.
                    </p>
                </div>
            </div>
        </>
    );
}
