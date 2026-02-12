import { FormEvent, useState } from 'react';
import { useForm } from '@inertiajs/react';

interface Props {
    token: string | null;
    error: string | null;
}

export default function SetPassword({ token, error: initialError }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        token: token || '',
        password: '',
        password_confirmation: '',
    });

    const [showPassword, setShowPassword] = useState(false);
    const [showConfirm, setShowConfirm] = useState(false);

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        post('/supplier/set-password');
    };

    if (initialError) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
                <div className="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
                    <div className="text-center">
                        <div className="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                            <svg
                                className="h-6 w-6 text-red-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <h3 className="mt-4 text-lg font-medium text-gray-900">
                            {initialError}
                        </h3>
                        <p className="mt-2 text-sm text-gray-500">
                            Por favor, solicita una nueva invitación al administrador.
                        </p>
                        <a
                            href="/"
                            className="mt-6 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
                        >
                            Volver al inicio
                        </a>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
            <div className="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
                <div className="text-center mb-8">
                    <h1 className="text-2xl font-bold text-gray-900">
                        Establecer Contraseña
                    </h1>
                    <p className="mt-2 text-sm text-gray-500">
                        Crea una contraseña segura para tu cuenta
                    </p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Password Field */}
                    <div>
                        <label
                            htmlFor="password"
                            className="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Contraseña
                        </label>
                        <div className="flex gap-2">
                            <input
                                id="password"
                                type={showPassword ? 'text' : 'password'}
                                name="password"
                                value={data.password}
                                onChange={(e) =>
                                    setData('password', e.target.value)
                                }
                                className={`flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 ${
                                    errors.password
                                        ? 'border-red-500'
                                        : 'border-gray-300'
                                }`}
                                placeholder="Mínimo 10 caracteres"
                                autoComplete="new-password"
                            />
                            <button
                                type="button"
                                onMouseDown={(e) => {
                                    e.preventDefault();
                                    setShowPassword(!showPassword);
                                }}
                                className="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                                tabIndex={-1}
                            >
                                {showPassword ? '👁️' : '👁️‍🗨️'}
                            </button>
                        </div>
                        {errors.password && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.password}
                            </p>
                        )}
                        <p className="mt-1 text-xs text-gray-500">
                            Mínimo 10 caracteres con mayúsculas, números y
                            símbolos
                        </p>
                    </div>

                    {/* Confirm Password Field */}
                    <div>
                        <label
                            htmlFor="password_confirmation"
                            className="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Confirmar Contraseña
                        </label>
                        <div className="flex gap-2">
                            <input
                                id="password_confirmation"
                                type={showConfirm ? 'text' : 'password'}
                                name="password_confirmation"
                                value={data.password_confirmation}
                                onChange={(e) =>
                                    setData('password_confirmation', e.target.value)
                                }
                                className={`flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 ${
                                    errors.password_confirmation
                                        ? 'border-red-500'
                                        : 'border-gray-300'
                                }`}
                                placeholder="Repite tu contraseña"
                                autoComplete="new-password"
                            />
                            <button
                                type="button"
                                onMouseDown={(e) => {
                                    e.preventDefault();
                                    setShowConfirm(!showConfirm);
                                }}
                                className="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                                tabIndex={-1}
                            >
                                {showConfirm ? '👁️' : '👁️‍🗨️'}
                            </button>
                        </div>
                        {errors.password_confirmation && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.password_confirmation}
                            </p>
                        )}
                    </div>

                    {/* Submit Button */}
                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium py-2 px-4 rounded-lg transition duration-200"
                    >
                        {processing
                            ? 'Estableciendo contraseña...'
                            : 'Establecer Contraseña'}
                    </button>
                </form>

                <p className="mt-6 text-center text-xs text-gray-500">
                    Al continuar, aceptas nuestros términos y condiciones
                </p>
            </div>
        </div>
    );
}
