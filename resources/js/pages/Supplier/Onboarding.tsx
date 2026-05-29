import { Head, useForm } from '@inertiajs/react';
import { Check, LoaderCircle } from 'lucide-react';
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
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import SupplierAuthLayout from '@/layouts/supplier/supplier-auth-layout';

interface Props {
    supplier: {
        name: string;
        email: string;
        address_street: string | null;
        address_number: string | null;
        address_neighborhood: string | null;
        address_city: string | null;
        address_country: string;
        address_zip: string | null;
        clabe_interbancaria: string | null;
    };
}

const STEPS = [
    {
        number: 1,
        title: 'Dirección',
        description: 'Información de ubicación',
    },
    {
        number: 2,
        title: 'Datos Bancarios',
        description: 'Información de transferencia',
    },
    {
        number: 3,
        title: 'Confirmación',
        description: 'Revisa tu información',
    },
];

export default function Onboarding({ supplier }: Props) {
    const [currentStep, setCurrentStep] = useState(1);
    const [confirmed, setConfirmed] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        address_street: supplier.address_street || '',
        address_number: supplier.address_number || '',
        address_neighborhood: supplier.address_neighborhood || '',
        address_city: supplier.address_city || '',
        address_country: supplier.address_country || 'Mexico',
        address_zip: supplier.address_zip || '',
        clabe_interbancaria: supplier.clabe_interbancaria || '',
        confirm: false,
    });

    const handleNext = () => {
        if (currentStep < 3) {
            setCurrentStep(currentStep + 1);
        }
    };

    const handlePrev = () => {
        if (currentStep > 1) {
            setCurrentStep(currentStep - 1);
        }
    };

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (confirmed) {
            post('/supplier/onboarding/submit', {
                preserveScroll: true,
                onError: (formErrors) => {
                    const step1Fields = [
                        'address_street',
                        'address_number',
                        'address_neighborhood',
                        'address_city',
                        'address_country',
                        'address_zip',
                    ];
                    const step2Fields = ['clabe_interbancaria'];

                    const errorKeys = Object.keys(formErrors);
                    if (
                        errorKeys.some((key) =>
                            step1Fields.includes(key),
                        )
                    ) {
                        setCurrentStep(1);
                    } else if (
                        errorKeys.some((key) =>
                            step2Fields.includes(key),
                        )
                    ) {
                        setCurrentStep(2);
                    }
                },
            });
        }
    };

    const formatAddress = () => {
        const parts = [
            data.address_street,
            data.address_number,
            data.address_neighborhood,
            data.address_city,
            data.address_zip,
            data.address_country,
        ].filter(Boolean);
        return parts.join(', ');
    };

    return (
        <>
            <Head title="Completa tu Perfil" />
            <SupplierAuthLayout maxWidth="lg">
                {/* Header */}
                <div className="text-center">
                    <h1 className="text-3xl font-bold tracking-tight">
                        Completa tu Perfil
                    </h1>
                    <p className="mt-2 text-muted-foreground">
                        Necesitamos algunos datos más para activar tu cuenta
                    </p>
                </div>

                {/* Progress Bar */}
                <div>
                    <div className="mb-2 flex items-center justify-between">
                        <span className="text-sm font-medium">
                            Paso {currentStep} de 3
                        </span>
                        <span className="text-sm text-muted-foreground">
                            {Math.round((currentStep / 3) * 100)}%
                        </span>
                    </div>
                    <div className="h-2 w-full rounded-full bg-muted">
                        <div
                            className="h-2 rounded-full bg-primary transition-all duration-300"
                            style={{
                                width: `${(currentStep / 3) * 100}%`,
                            }}
                        />
                    </div>
                </div>

                {/* Steps Indicator */}
                <div className="grid grid-cols-3 gap-4">
                    {STEPS.map((step) => (
                        <div
                            key={step.number}
                            className={`rounded-lg border p-3 text-center transition ${
                                currentStep === step.number
                                    ? 'border-primary bg-primary/10'
                                    : currentStep > step.number
                                      ? 'border-green-600 bg-green-50 dark:bg-green-950/20'
                                      : 'border-border bg-muted'
                            }`}
                        >
                            <div className="flex items-center justify-center gap-1.5">
                                {currentStep > step.number && (
                                    <Check className="size-3.5 text-green-600" />
                                )}
                                <span className="text-sm font-semibold">
                                    {step.title}
                                </span>
                            </div>
                            <div className="text-xs text-muted-foreground">
                                {step.description}
                            </div>
                        </div>
                    ))}
                </div>

                {/* Form */}
                <Card>
                    <CardHeader>
                        <CardTitle>{STEPS[currentStep - 1].title}</CardTitle>
                        <CardDescription>
                            {STEPS[currentStep - 1].description}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit}>
                            {/* STEP 1: Address */}
                            {currentStep === 1 && (
                                <div className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="grid gap-2">
                                            <Label htmlFor="address_street">
                                                Calle
                                            </Label>
                                            <Input
                                                id="address_street"
                                                type="text"
                                                value={data.address_street}
                                                onChange={(e) =>
                                                    setData(
                                                        'address_street',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="Avenida Paseo de la Reforma"
                                            />
                                            <InputError
                                                message={errors.address_street}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="address_number">
                                                Número
                                            </Label>
                                            <Input
                                                id="address_number"
                                                type="text"
                                                value={data.address_number}
                                                onChange={(e) =>
                                                    setData(
                                                        'address_number',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="505"
                                            />
                                            <InputError
                                                message={errors.address_number}
                                            />
                                        </div>
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="address_neighborhood">
                                            Barrio/Colonia
                                        </Label>
                                        <Input
                                            id="address_neighborhood"
                                            type="text"
                                            value={data.address_neighborhood}
                                            onChange={(e) =>
                                                setData(
                                                    'address_neighborhood',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="Cuauhtémoc"
                                        />
                                        <InputError
                                            message={
                                                errors.address_neighborhood
                                            }
                                        />
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="grid gap-2">
                                            <Label htmlFor="address_city">
                                                Ciudad
                                            </Label>
                                            <Input
                                                id="address_city"
                                                type="text"
                                                value={data.address_city}
                                                onChange={(e) =>
                                                    setData(
                                                        'address_city',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="Ciudad de México"
                                            />
                                            <InputError
                                                message={errors.address_city}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="address_zip">
                                                Código Postal
                                            </Label>
                                            <Input
                                                id="address_zip"
                                                type="text"
                                                value={data.address_zip}
                                                onChange={(e) =>
                                                    setData(
                                                        'address_zip',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="06500"
                                                maxLength={5}
                                            />
                                            <InputError
                                                message={errors.address_zip}
                                            />
                                        </div>
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="address_country">
                                            País
                                        </Label>
                                        <Select
                                            value={data.address_country}
                                            onValueChange={(value) =>
                                                setData(
                                                    'address_country',
                                                    value,
                                                )
                                            }
                                        >
                                            <SelectTrigger id="address_country">
                                                <SelectValue placeholder="Selecciona un país" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="Mexico">
                                                    México
                                                </SelectItem>
                                                <SelectItem value="USA">
                                                    Estados Unidos
                                                </SelectItem>
                                                <SelectItem value="Canada">
                                                    Canadá
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError
                                            message={errors.address_country}
                                        />
                                    </div>
                                </div>
                            )}

                            {/* STEP 2: Banking */}
                            {currentStep === 2 && (
                                <div className="space-y-4">
                                    <div className="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950/20">
                                        <p className="text-sm text-blue-900 dark:text-blue-300">
                                            Tu CLABE Interbancaria (18 dígitos)
                                            es necesaria para la correcta
                                            programación de tus pagos. Esta
                                            información se maneja de forma
                                            confidencial y se utiliza
                                            exclusivamente para realizar
                                            transferencias a tu cuenta.
                                        </p>
                                        <p className="mt-2 text-xs text-blue-700 dark:text-blue-400">
                                            Si requieres consultar nuestro aviso
                                            de privacidad, puedes solicitarlo a
                                            tu administrador o escribirnos a{' '}
                                            <a
                                                href="mailto:administracion@grupocosteno.com"
                                                className="underline"
                                            >
                                                administracion@grupocosteno.com
                                            </a>
                                            .
                                        </p>
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="clabe_interbancaria">
                                            CLABE Interbancaria
                                        </Label>
                                        <Input
                                            id="clabe_interbancaria"
                                            type="text"
                                            value={data.clabe_interbancaria}
                                            onChange={(e) =>
                                                setData(
                                                    'clabe_interbancaria',
                                                    e.target.value.replace(
                                                        /\D/g,
                                                        '',
                                                    ),
                                                )
                                            }
                                            placeholder="002011111111111111"
                                            maxLength={18}
                                            className="font-mono"
                                        />
                                        <InputError
                                            message={errors.clabe_interbancaria}
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            18 dígitos numéricos
                                        </p>
                                    </div>
                                </div>
                            )}

                            {/* STEP 3: Confirmation */}
                            {currentStep === 3 && (
                                <div className="space-y-4">
                                    {Object.keys(errors).length > 0 && (
                                        <div className="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950/20">
                                            <p className="mb-1 text-sm font-semibold text-red-900 dark:text-red-300">
                                                Corrige los siguientes errores:
                                            </p>
                                            <ul className="list-inside list-disc text-sm text-red-700 dark:text-red-400">
                                                {Object.values(errors).map(
                                                    (error, i) => (
                                                        <li key={i}>
                                                            {error}
                                                        </li>
                                                    ),
                                                )}
                                            </ul>
                                        </div>
                                    )}
                                    <div className="space-y-3">
                                        <div className="rounded-lg bg-muted p-4">
                                            <h3 className="mb-2 text-sm font-semibold">
                                                Dirección
                                            </h3>
                                            <p className="text-sm text-muted-foreground">
                                                {formatAddress()}
                                            </p>
                                        </div>

                                        <div className="rounded-lg bg-muted p-4">
                                            <h3 className="mb-2 text-sm font-semibold">
                                                CLABE
                                            </h3>
                                            <p className="font-mono text-sm text-muted-foreground">
                                                {data.clabe_interbancaria
                                                    .slice(0, -4)
                                                    .replace(/./g, '*')}
                                                {data.clabe_interbancaria.slice(
                                                    -4,
                                                )}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex items-start gap-3 rounded-lg border border-primary/20 bg-primary/5 p-4">
                                        <Checkbox
                                            id="confirm"
                                            className="mt-0.5"
                                            checked={confirmed}
                                            onCheckedChange={(checked) => {
                                                setConfirmed(checked === true);
                                                setData(
                                                    'confirm',
                                                    checked === true,
                                                );
                                            }}
                                        />
                                        <Label
                                            htmlFor="confirm"
                                            className="text-sm font-normal leading-relaxed text-foreground"
                                        >
                                            Declaro bajo protesta de decir
                                            verdad que toda la información
                                            proporcionada es veraz y correcta.
                                            Autorizo el uso de mis datos
                                            personales, fiscales y bancarios
                                            para los fines de alta como
                                            proveedor, programación de pagos y
                                            gestión administrativa. Me
                                            comprometo a notificar cualquier
                                            cambio en mi información y asumo la
                                            responsabilidad por la exactitud de
                                            los datos aquí proporcionados.
                                        </Label>
                                    </div>
                                    <InputError message={errors.confirm} />
                                </div>
                            )}

                            {/* Navigation Buttons */}
                            <div className="mt-8 flex gap-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    className="flex-1"
                                    onClick={handlePrev}
                                    disabled={currentStep === 1 || processing}
                                >
                                    &larr; Anterior
                                </Button>

                                {currentStep < 3 ? (
                                    <Button
                                        type="button"
                                        className="flex-1"
                                        onClick={handleNext}
                                        disabled={processing}
                                    >
                                        Siguiente &rarr;
                                    </Button>
                                ) : (
                                    <Button
                                        type="submit"
                                        className="flex-1"
                                        disabled={!confirmed || processing}
                                    >
                                        {processing && (
                                            <LoaderCircle className="size-4 animate-spin" />
                                        )}
                                        {processing
                                            ? 'Completando...'
                                            : 'Completar Onboarding'}
                                    </Button>
                                )}
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </SupplierAuthLayout>
        </>
    );
}
