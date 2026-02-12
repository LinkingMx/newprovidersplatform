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
        <div className="min-h-screen bg-gray-50 py-12 px-4">
            <div className="max-w-2xl mx-auto">
                {/* Header */}
                <div className="text-center mb-8">
                    <h1 className="text-3xl font-bold text-gray-900">
                        Completa tu Perfil
                    </h1>
                    <p className="mt-2 text-gray-600">
                        Necesitamos algunos datos más para activar tu cuenta
                    </p>
                </div>

                {/* Progress Bar */}
                <div className="mb-8">
                    <div className="flex items-center justify-between mb-2">
                        <span className="text-sm font-medium text-gray-700">
                            Paso {currentStep} de 3
                        </span>
                        <span className="text-sm text-gray-500">
                            {Math.round((currentStep / 3) * 100)}%
                        </span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2">
                        <div
                            className="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                            style={{ width: `${(currentStep / 3) * 100}%` }}
                        />
                    </div>
                </div>

                {/* Steps Indicator */}
                <div className="grid grid-cols-3 gap-4 mb-8">
                    {STEPS.map((step) => (
                        <div
                            key={step.number}
                            className={`p-3 rounded-lg text-center transition ${
                                currentStep === step.number
                                    ? 'bg-indigo-100 border border-indigo-600'
                                    : currentStep > step.number
                                      ? 'bg-green-100 border border-green-600'
                                      : 'bg-gray-100 border border-gray-300'
                            }`}
                        >
                            <div className="font-semibold text-sm text-gray-900">
                                {step.title}
                            </div>
                            <div className="text-xs text-gray-600">
                                {step.description}
                            </div>
                        </div>
                    ))}
                </div>

                {/* Form */}
                <form onSubmit={handleSubmit} className="bg-white rounded-lg shadow p-8">
                    {/* STEP 1: Address */}
                    {currentStep === 1 && (
                        <div className="space-y-6">
                            <h2 className="text-lg font-semibold text-gray-900">
                                Dirección
                            </h2>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Calle
                                    </label>
                                    <input
                                        type="text"
                                        value={data.address_street}
                                        onChange={(e) =>
                                            setData(
                                                'address_street',
                                                e.target.value
                                            )
                                        }
                                        placeholder="Avenida Paseo de la Reforma"
                                        className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-900 ${
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
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Número
                                    </label>
                                    <input
                                        type="text"
                                        value={data.address_number}
                                        onChange={(e) =>
                                            setData(
                                                'address_number',
                                                e.target.value
                                            )
                                        }
                                        placeholder="505"
                                        className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-900 ${
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
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Barrio/Colonia
                                </label>
                                <input
                                    type="text"
                                    value={data.address_neighborhood}
                                    onChange={(e) =>
                                        setData(
                                            'address_neighborhood',
                                            e.target.value
                                        )
                                    }
                                    placeholder="Cuauhtémoc"
                                    className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-900 ${
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
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Ciudad
                                    </label>
                                    <input
                                        type="text"
                                        value={data.address_city}
                                        onChange={(e) =>
                                            setData('address_city', e.target.value)
                                        }
                                        placeholder="Ciudad de México"
                                        className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-900 ${
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
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Código Postal
                                    </label>
                                    <input
                                        type="text"
                                        value={data.address_zip}
                                        onChange={(e) =>
                                            setData('address_zip', e.target.value)
                                        }
                                        placeholder="06500"
                                        maxLength={5}
                                        className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-900 ${
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
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    País
                                </label>
                                <select
                                    value={data.address_country}
                                    onChange={(e) =>
                                        setData('address_country', e.target.value)
                                    }
                                    className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-900 ${
                                        errors.address_country
                                            ? 'border-red-500'
                                            : 'border-gray-300'
                                    }`}
                                >
                                    <option value="Mexico">México</option>
                                    <option value="USA">
                                        Estados Unidos
                                    </option>
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

                            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <p className="text-sm text-blue-900">
                                    <strong>CLABE Interbancaria:</strong> Es un
                                    código de 18 dígitos único para transferencias
                                    bancarias en México.{' '}
                                    <a
                                        href="#"
                                        className="text-blue-600 hover:underline"
                                    >
                                        ¿Dónde lo encuentro?
                                    </a>
                                </p>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    CLABE Interbancaria
                                </label>
                                <input
                                    type="text"
                                    value={data.clabe_interbancaria}
                                    onChange={(e) =>
                                        setData(
                                            'clabe_interbancaria',
                                            e.target.value.replace(/\D/g, '')
                                        )
                                    }
                                    placeholder="002011111111111111"
                                    maxLength={18}
                                    className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-900 font-mono ${
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
                                <div className="bg-gray-50 rounded-lg p-4">
                                    <h3 className="font-semibold text-gray-900 mb-2">
                                        Dirección
                                    </h3>
                                    <p className="text-sm text-gray-700">
                                        {formatAddress()}
                                    </p>
                                </div>

                                <div className="bg-gray-50 rounded-lg p-4">
                                    <h3 className="font-semibold text-gray-900 mb-2">
                                        CLABE
                                    </h3>
                                    <p className="text-sm font-mono text-gray-700">
                                        {data.clabe_interbancaria.slice(0, -4)
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
                                    className="h-4 w-4 border-gray-300 rounded text-indigo-600"
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
                    <div className="flex gap-4 mt-8">
                        <button
                            type="button"
                            onClick={handlePrev}
                            disabled={currentStep === 1 || processing}
                            className="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed font-medium transition"
                        >
                            ← Anterior
                        </button>

                        {currentStep < 3 ? (
                            <button
                                type="button"
                                onClick={handleNext}
                                disabled={processing}
                                className="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium transition"
                            >
                                Siguiente →
                            </button>
                        ) : (
                            <button
                                type="submit"
                                disabled={!confirmed || processing}
                                className="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium transition"
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
