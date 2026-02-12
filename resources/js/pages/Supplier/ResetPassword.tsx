import { Head, Link, useForm } from '@inertiajs/react';
import { Eye, EyeOff, LoaderCircle } from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
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

interface Props {
    token: string | null;
    email: string | null;
    error: string | null;
}

export default function ResetPassword({ token, email, error }: Props) {
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmation, setShowConfirmation] = useState(false);
    const { data, setData, post, processing, errors } = useForm({
        token: token ?? '',
        email: email ?? '',
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        post('/supplier/reset-password');
    };

    if (error) {
        return (
            <>
                <Head title="Enlace Inválido" />
                <div className="flex min-h-screen flex-col items-center justify-center bg-background p-6 lg:p-8">
                    <div className="w-full max-w-md space-y-6">
                        <div className="flex justify-center">
                            <BrandLogo className="h-12" />
                        </div>

                        <Card>
                            <CardHeader className="text-center">
                                <CardTitle className="text-2xl">
                                    Enlace Inválido
                                </CardTitle>
                                <CardDescription>{error}</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Button asChild className="w-full">
                                    <Link href="/supplier/forgot-password">
                                        Solicitar nuevo enlace
                                    </Link>
                                </Button>
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

    return (
        <>
            <Head title="Nueva Contraseña" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-background p-6 lg:p-8">
                <div className="w-full max-w-md space-y-6">
                    <div className="flex justify-center">
                        <BrandLogo className="h-12" />
                    </div>

                    <Card>
                        <CardHeader className="text-center">
                            <CardTitle className="text-2xl">
                                Nueva Contraseña
                            </CardTitle>
                            <CardDescription>
                                Ingresa tu nueva contraseña. Debe tener al menos
                                10 caracteres, incluir mayúsculas, números y
                                símbolos especiales.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-6">
                                <input
                                    type="hidden"
                                    name="token"
                                    value={data.token}
                                />
                                <input
                                    type="hidden"
                                    name="email"
                                    value={data.email}
                                />

                                <InputError message={errors.token} />

                                {/* Password Field */}
                                <div className="grid gap-2">
                                    <Label htmlFor="password">
                                        Nueva Contraseña
                                    </Label>
                                    <div className="relative">
                                        <Input
                                            id="password"
                                            type={
                                                showPassword
                                                    ? 'text'
                                                    : 'password'
                                            }
                                            name="password"
                                            value={data.password}
                                            onChange={(e) =>
                                                setData(
                                                    'password',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;"
                                            required
                                            autoComplete="new-password"
                                            autoFocus
                                            className="pr-10"
                                        />
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            className="absolute top-0 right-0 h-full px-3 hover:bg-transparent"
                                            onMouseDown={(e) => {
                                                e.preventDefault();
                                                setShowPassword(!showPassword);
                                            }}
                                            tabIndex={-1}
                                        >
                                            {showPassword ? (
                                                <EyeOff className="size-4 text-muted-foreground" />
                                            ) : (
                                                <Eye className="size-4 text-muted-foreground" />
                                            )}
                                        </Button>
                                    </div>
                                    <InputError message={errors.password} />
                                </div>

                                {/* Confirm Password Field */}
                                <div className="grid gap-2">
                                    <Label htmlFor="password_confirmation">
                                        Confirmar Contraseña
                                    </Label>
                                    <div className="relative">
                                        <Input
                                            id="password_confirmation"
                                            type={
                                                showConfirmation
                                                    ? 'text'
                                                    : 'password'
                                            }
                                            name="password_confirmation"
                                            value={data.password_confirmation}
                                            onChange={(e) =>
                                                setData(
                                                    'password_confirmation',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;"
                                            required
                                            autoComplete="new-password"
                                            className="pr-10"
                                        />
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            className="absolute top-0 right-0 h-full px-3 hover:bg-transparent"
                                            onMouseDown={(e) => {
                                                e.preventDefault();
                                                setShowConfirmation(
                                                    !showConfirmation,
                                                );
                                            }}
                                            tabIndex={-1}
                                        >
                                            {showConfirmation ? (
                                                <EyeOff className="size-4 text-muted-foreground" />
                                            ) : (
                                                <Eye className="size-4 text-muted-foreground" />
                                            )}
                                        </Button>
                                    </div>
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
                                        ? 'Restableciendo...'
                                        : 'Restablecer Contraseña'}
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
