import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { Eye, EyeOff, LoaderCircle } from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
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
import SupplierAuthLayout from '@/layouts/supplier/supplier-auth-layout';

export default function Login() {
    const { flash } = usePage<{ flash: { status?: string } }>().props;
    const [showPassword, setShowPassword] = useState(false);
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
    });

    const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        post('/supplier/login');
    };

    return (
        <>
            <Head title="Acceso de Proveedores" />
            <SupplierAuthLayout
                maxWidth="md"
                backLink={{ href: '/', label: 'Volver al inicio' }}
            >
                <Card>
                    <CardHeader className="text-center">
                        <CardTitle className="text-2xl">
                            Acceso de Proveedores
                        </CardTitle>
                        <CardDescription>
                            Ingresa con tu correo y contrase&ntilde;a
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
                                    Correo Electr&oacute;nico
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

                            <div className="grid gap-2">
                                <div className="flex items-center justify-between">
                                    <Label htmlFor="password">
                                        Contrase&ntilde;a
                                    </Label>
                                    <Link
                                        href="/supplier/forgot-password"
                                        className="text-sm text-muted-foreground underline-offset-4 hover:underline"
                                    >
                                        &iquest;Olvidaste tu contrase&ntilde;a?
                                    </Link>
                                </div>
                                <div className="relative">
                                    <Input
                                        id="password"
                                        type={
                                            showPassword ? 'text' : 'password'
                                        }
                                        name="password"
                                        value={data.password}
                                        onChange={(e) =>
                                            setData('password', e.target.value)
                                        }
                                        placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;"
                                        required
                                        autoComplete="current-password"
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

                            <Button
                                type="submit"
                                className="w-full"
                                disabled={processing}
                            >
                                {processing && (
                                    <LoaderCircle className="size-4 animate-spin" />
                                )}
                                {processing ? 'Accediendo...' : 'Acceder'}
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </SupplierAuthLayout>
        </>
    );
}
