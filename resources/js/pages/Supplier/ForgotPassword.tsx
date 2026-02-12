import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import type { FormEvent } from 'react';
import BrandLogo from '@/components/brand-logo';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export default function ForgotPassword() {
    const { flash } = usePage<{ flash: { status?: string } }>().props;
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        post('/supplier/forgot-password');
    };

    return (
        <>
            <Head title="Restablecer Contraseña" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-background p-6 lg:p-8">
                <div className="w-full max-w-md space-y-6">
                    <div className="flex justify-center">
                        <BrandLogo className="h-12" />
                    </div>

                    <Card>
                        <CardHeader className="text-center">
                            <CardTitle className="text-2xl">
                                Restablecer Contraseña
                            </CardTitle>
                            <CardDescription>
                                Ingresa tu correo electrónico y te enviaremos un
                                enlace para restablecer tu contraseña.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {flash?.status && (
                                <div className="mb-4 rounded-md bg-green-50 p-3 text-center text-sm font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">
                                    {flash.status}
                                </div>
                            )}

                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div className="grid gap-2">
                                    <Label htmlFor="email">
                                        Correo Electrónico
                                    </Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        value={data.email}
                                        onChange={(e) =>
                                            setData('email', e.target.value)
                                        }
                                        placeholder="tu@correo.com"
                                        required
                                        autoComplete="email"
                                        autoFocus
                                    />
                                    <InputError message={errors.email} />
                                </div>

                                <Button
                                    type="submit"
                                    className="w-full"
                                    disabled={processing}
                                >
                                    {processing && (
                                        <LoaderCircle className="size-4 animate-spin" />
                                    )}
                                    {processing
                                        ? 'Enviando...'
                                        : 'Enviar enlace de restablecimiento'}
                                </Button>
                            </form>
                        </CardContent>
                    </Card>

                    <div className="text-center">
                        <Button asChild variant="link" size="sm">
                            <Link href="/supplier/login">
                                &larr; Volver al inicio de sesión
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </>
    );
}
