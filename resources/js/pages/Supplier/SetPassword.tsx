import { FormEvent, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { AlertCircle, Eye, EyeOff, LoaderCircle } from 'lucide-react';
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
import InputError from '@/components/input-error';

interface Props {
    token: string | null;
    error: string | null;
}

export default function SetPassword({ token, error: initialError }: Props) {
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirm, setShowConfirm] = useState(false);
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState<Record<string, string | string[]>>({});

    const getError = (field: string): string | undefined => {
        const error = errors[field];
        if (!error) return undefined;
        return Array.isArray(error) ? error[0] : error;
    };

    const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        setProcessing(true);
        setErrors({});

        try {
            const csrfToken =
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') || '';

            const response = await fetch('/supplier/auth/set-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken,
                },
                body: JSON.stringify({
                    token: token || '',
                    password,
                    password_confirmation: passwordConfirmation,
                }),
                credentials: 'same-origin',
            });

            const contentType = response.headers.get('content-type');
            let data: any = {};

            if (contentType?.includes('application/json')) {
                data = await response.json();
            }

            if (!response.ok) {
                if (response.status === 422) {
                    setErrors(
                        data.errors || { password: 'Validación fallida' },
                    );
                } else if (response.status === 419) {
                    window.location.reload();
                    return;
                } else {
                    setErrors(
                        data.errors || {
                            general:
                                data.message ||
                                'Error al procesar la solicitud',
                        },
                    );
                }
                setProcessing(false);
                return;
            }

            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.href = '/supplier/onboarding';
            }
        } catch (error) {
            console.error('Form submission error:', error);
            setErrors({ general: 'Error de conexión' });
            setProcessing(false);
        }
    };

    if (initialError) {
        return (
            <>
                <Head title="Enlace Inválido" />
                <div className="flex min-h-screen flex-col items-center justify-center bg-background p-6 lg:p-8">
                    <div className="w-full max-w-md space-y-6">
                        <Card>
                            <CardHeader className="text-center">
                                <div className="mx-auto mb-2 flex size-12 items-center justify-center rounded-full bg-destructive/10">
                                    <AlertCircle className="size-6 text-destructive" />
                                </div>
                                <CardTitle className="text-2xl">
                                    Enlace Inválido
                                </CardTitle>
                                <CardDescription>
                                    {initialError}
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <p className="text-center text-sm text-muted-foreground">
                                    Por favor, solicita una nueva invitación al
                                    administrador.
                                </p>
                                <Button asChild className="w-full">
                                    <Link href="/">Volver al inicio</Link>
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </>
        );
    }

    return (
        <>
            <Head title="Establecer Contraseña" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-background p-6 lg:p-8">
                <div className="w-full max-w-md space-y-6">
                    <Card>
                        <CardHeader className="text-center">
                            <CardTitle className="text-2xl">
                                Establecer Contraseña
                            </CardTitle>
                            <CardDescription>
                                Crea una contraseña segura para tu cuenta. Debe
                                tener al menos 10 caracteres, incluir
                                mayúsculas, números y símbolos especiales.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <InputError message={getError('general')} />

                            <form onSubmit={handleSubmit} className="space-y-6">
                                {/* Password Field */}
                                <div className="grid gap-2">
                                    <Label htmlFor="password">Contraseña</Label>
                                    <div className="relative">
                                        <Input
                                            id="password"
                                            type={
                                                showPassword
                                                    ? 'text'
                                                    : 'password'
                                            }
                                            name="password"
                                            value={password}
                                            onChange={(e) =>
                                                setPassword(e.target.value)
                                            }
                                            placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;"
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
                                    <InputError
                                        message={getError('password')}
                                    />
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
                                                showConfirm
                                                    ? 'text'
                                                    : 'password'
                                            }
                                            name="password_confirmation"
                                            value={passwordConfirmation}
                                            onChange={(e) =>
                                                setPasswordConfirmation(
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;"
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
                                                setShowConfirm(!showConfirm);
                                            }}
                                            tabIndex={-1}
                                        >
                                            {showConfirm ? (
                                                <EyeOff className="size-4 text-muted-foreground" />
                                            ) : (
                                                <Eye className="size-4 text-muted-foreground" />
                                            )}
                                        </Button>
                                    </div>
                                    <InputError
                                        message={getError(
                                            'password_confirmation',
                                        )}
                                    />
                                </div>

                                {/* Submit Button */}
                                <Button
                                    type="submit"
                                    className="w-full"
                                    disabled={processing}
                                >
                                    {processing && (
                                        <LoaderCircle className="size-4 animate-spin" />
                                    )}
                                    {processing
                                        ? 'Estableciendo contraseña...'
                                        : 'Establecer Contraseña'}
                                </Button>
                            </form>
                        </CardContent>
                    </Card>

                    <p className="text-center text-xs text-muted-foreground">
                        Al continuar, aceptas nuestros términos y condiciones
                    </p>

                    <div className="text-center">
                        <Button asChild variant="link" size="sm">
                            <Link href="/">&larr; Volver al inicio</Link>
                        </Button>
                    </div>
                </div>
            </div>
        </>
    );
}
