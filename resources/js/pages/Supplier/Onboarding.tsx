import { FormEvent, useState } from 'react';
import { useForm } from '@inertiajs/react';

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
            post(route('supplier.onboarding.submit'));
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
        <div className="min-h-screen bg-gray-50 px-4 py-12">
            <div className="mx-auto max-w-2xl">
                {/* Header */}
                <div className="mb-8 text-center">
                    <h1 className="text-3xl font-bold text-gray-900">
                        Completa tu Perfil
                    </h1>
                    <p className="mt-2 text-gray-600">
                        Necesitamos algunos datos más para activar tu cuenta
                    </p>
                </div>

                {/* Progress Bar */}
                <div className="mb-8">
                    <div className="mb-2 flex items-center justify-between">
                        <span className="text-sm font-medium text-gray-700">
                            Paso {currentStep} de 3
                        </span>
                        <span className="text-sm text-gray-500">
                            {Math.round((currentStep / 3) * 100)}%
                        </span>
                    </div>
                    <div className="h-2 w-full rounded-full bg-gray-200">
                        <div
                            className="h-2 rounded-full bg-indigo-600 transition-all duration-300"
                            style={{ width: `${(currentStep / 3) * 100}%` }}
                        />
                    </div>
                </div>

                {/* Steps Indicator */}
                <div className="mb-8 grid grid-cols-3 gap-4">
                    {STEPS.map((step) => (
                        <div
                            key={step.number}
                            className={`rounded-lg p-3 text-center transition ${
                                currentStep === step.number
                                    ? 'border border-indigo-600 bg-indigo-100'
                                    : currentStep > step.number
                                      ? 'border border-green-600 bg-green-100'
                                      : 'border border-gray-300 bg-gray-100'
                            }`}
                        >
                            <div className="text-sm font-semibold text-gray-900">
                                {step.title}
                            </div>
                            <div className="text-xs text-gray-600">
                                {step.description}
                            </div>
                        </div>
                    ))}
                </div>

                {/* Form */}
                <form
                    onSubmit={handleSubmit}
                    className="rounded-lg bg-white p-8 shadow"
                >
                    {/* STEP 1: Address */}
                    {currentStep === 1 && (
                        <div className="space-y-6">
                            <h2 className="text-lg font-semibold text-gray-900">
                                Dirección
                            </h2>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="mb-2 block text-sm font-medium text-gray-700">
                                        Calle
                                    </label>
                                    <input
                                        type="text"
                                        value={data.address_street}
                                        onChange={(e) =>
                                            setData(
                                                'address_street',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Avenida Paseo de la Reforma"
                                        className={`w-full rounded-lg border px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none ${
                                            errors.address_street
                                                ? 'border-red-500'
                                                : 'border-gray-300'
                                        }`}
                                    />
                                    {errors.address_street && (
                                        <p className="mt-1 text-sm text-red-600">
                                            {errors.address_street}
                                        </p>
                                    )}
                                </div>

                                <div>
                                    <label className="mb-2 block text-sm font-medium text-gray-700">
                                        Número
                                    </label>
                                    <input
                                        type="text"
                                        value={data.address_number}
                                        onChange={(e) =>
                                            setData(
                                                'address_number',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="505"
                                        className={`w-full rounded-lg border px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none ${
                                            errors.address_number
                                                ? 'border-red-500'
                                                : 'border-gray-300'
                                        }`}
                                    />
                                    {errors.address_number && (
                                        <p className="mt-1 text-sm text-red-600">
                                            {errors.address_number}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-medium text-gray-700">
                                    Barrio/Colonia
                                </label>
                                <input
                                    type="text"
                                    value={data.address_neighborhood}
                                    onChange={(e) =>
                                        setData(
                                            'address_neighborhood',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="Cuauhtémoc"
                                    className={`w-full rounded-lg border px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none ${
                                        errors.address_neighborhood
                                            ? 'border-red-500'
                                            : 'border-gray-300'
                                    }`}
                                />
                                {errors.address_neighborhood && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.address_neighborhood}
                                    </p>
                                )}
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="mb-2 block text-sm font-medium text-gray-700">
                                        Ciudad
                                    </label>
                                    <input
                                        type="text"
                                        value={data.address_city}
                                        onChange={(e) =>
                                            setData(
                                                'address_city',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Ciudad de México"
                                        className={`w-full rounded-lg border px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none ${
                                            errors.address_city
                                                ? 'border-red-500'
                                                : 'border-gray-300'
                                        }`}
                                    />
                                    {errors.address_city && (
                                        <p className="mt-1 text-sm text-red-600">
                                            {errors.address_city}
                                        </p>
                                    )}
                                </div>

                                <div>
                                    <label className="mb-2 block text-sm font-medium text-gray-700">
                                        Código Postal
                                    </label>
                                    <input
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
                                        className={`w-full rounded-lg border px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none ${
                                            errors.address_zip
                                                ? 'border-red-500'
                                                : 'border-gray-300'
                                        }`}
                                    />
                                    {errors.address_zip && (
                                        <p className="mt-1 text-sm text-red-600">
                                            {errors.address_zip}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-medium text-gray-700">
                                    País
                                </label>
                                <select
                                    value={data.address_country}
                                    onChange={(e) =>
                                        setData(
                                            'address_country',
                                            e.target.value,
                                        )
                                    }
                                    className={`w-full rounded-lg border px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none ${
                                        errors.address_country
                                            ? 'border-red-500'
                                            : 'border-gray-300'
                                    }`}
                                >
                                    <option value="Mexico">México</option>
                                    <option value="USA">Estados Unidos</option>
                                    <option value="Canada">Canadá</option>
                                </select>
                                {errors.address_country && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.address_country}
                                    </p>
                                )}
                            </div>
                        </div>
                    )}

                    {/* STEP 2: Banking */}
                    {currentStep === 2 && (
                        <div className="space-y-6">
                            <h2 className="text-lg font-semibold text-gray-900">
                                Datos Bancarios
                            </h2>

                            <div className="rounded-lg border border-blue-200 bg-blue-50 p-4">
                                <p className="text-sm text-blue-900">
                                    <strong>CLABE Interbancaria:</strong> Es un
                                    código de 18 dígitos único para
                                    transferencias bancarias en México.{' '}
                                    <a
                                        href="#"
                                        className="text-blue-600 hover:underline"
                                    >
                                        ¿Dónde lo encuentro?
                                    </a>
                                </p>
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-medium text-gray-700">
                                    CLABE Interbancaria
                                </label>
                                <input
                                    type="text"
                                    value={data.clabe_interbancaria}
                                    onChange={(e) =>
                                        setData(
                                            'clabe_interbancaria',
                                            e.target.value.replace(/\D/g, ''),
                                        )
                                    }
                                    placeholder="002011111111111111"
                                    maxLength={18}
                                    className={`w-full rounded-lg border px-4 py-2 font-mono text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none ${
                                        errors.clabe_interbancaria
                                            ? 'border-red-500'
                                            : 'border-gray-300'
                                    }`}
                                />
                                {errors.clabe_interbancaria && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.clabe_interbancaria}
                                    </p>
                                )}
                                <p className="mt-1 text-xs text-gray-500">
                                    18 dígitos numéricos
                                </p>
                            </div>
                        </div>
                    )}

                    {/* STEP 3: Confirmation */}
                    {currentStep === 3 && (
                        <div className="space-y-6">
                            <h2 className="text-lg font-semibold text-gray-900">
                                Confirmación
                            </h2>

                            <div className="space-y-4">
                                <div className="rounded-lg bg-gray-50 p-4">
                                    <h3 className="mb-2 font-semibold text-gray-900">
                                        Dirección
                                    </h3>
                                    <p className="text-sm text-gray-700">
                                        {formatAddress()}
                                    </p>
                                </div>

                                <div className="rounded-lg bg-gray-50 p-4">
                                    <h3 className="mb-2 font-semibold text-gray-900">
                                        CLABE
                                    </h3>
                                    <p className="font-mono text-sm text-gray-700">
                                        {data.clabe_interbancaria
                                            .slice(0, -4)
                                            .replace(/./g, '*')}
                                        {data.clabe_interbancaria.slice(-4)}
                                    </p>
                                </div>
                            </div>

                            <div className="flex items-center space-x-2">
                                <input
                                    id="confirm"
                                    type="checkbox"
                                    checked={confirmed}
                                    onChange={(e) =>
                                        setConfirmed(e.target.checked)
                                    }
                                    className="h-4 w-4 rounded border-gray-300 text-indigo-600"
                                />
                                <label
                                    htmlFor="confirm"
                                    className="text-sm text-gray-700"
                                >
                                    Confirmo que toda la información es correcta
                                </label>
                            </div>
                            {errors.confirm && (
                                <p className="text-sm text-red-600">
                                    {errors.confirm}
                                </p>
                            )}
                        </div>
                    )}

                    {/* Navigation Buttons */}
                    <div className="mt-8 flex gap-4">
                        <button
                            type="button"
                            onClick={handlePrev}
                            disabled={currentStep === 1 || processing}
                            className="flex-1 rounded-lg border border-gray-300 px-4 py-2 font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            ← Anterior
                        </button>

                        {currentStep < 3 ? (
                            <button
                                type="button"
                                onClick={handleNext}
                                disabled={processing}
                                className="flex-1 rounded-lg bg-indigo-600 px-4 py-2 font-medium text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Siguiente →
                            </button>
                        ) : (
                            <button
                                type="submit"
                                disabled={!confirmed || processing}
                                className="flex-1 rounded-lg bg-green-600 px-4 py-2 font-medium text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {processing
                                    ? 'Completando...'
                                    : 'Completar Onboarding'}
                            </button>
                        )}
                    </div>
                </form>
            </div>
        </div>
    );
}
