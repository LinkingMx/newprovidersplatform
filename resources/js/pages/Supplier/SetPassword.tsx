import { FormEvent, useState } from 'react';
import { useForm } from '@inertiajs/react';
import { router } from '@inertiajs/react';

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
        post(route('supplier.set-password.store'));
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
                            className="block text-sm font-medium text-gray-700"
                        >
                            Contraseña
                        </label>
                        <div className="mt-1 relative">
                            <input
                                id="password"
                                type={showPassword ? 'text' : 'password'}
                                name="password"
                                value={data.password}
                                onChange={(e) =>
                                    setData('password', e.target.value)
                                }
                                className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 pr-10 ${
                                    errors.password
                                        ? 'border-red-500'
                                        : 'border-gray-300'
                                }`}
                                placeholder="Mínimo 10 caracteres"
                                disabled={false}
                                autoComplete="new-password"
                            />
                            <button
                                type="button"
                                onClick={() => setShowPassword(!showPassword)}
                                className="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600"
                            >
                                {showPassword ? (
                                    <svg
                                        className="h-5 w-5"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path
                                            fillRule="evenodd"
                                            d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                            clipRule="evenodd"
                                        />
                                    </svg>
                                ) : (
                                    <svg
                                        className="h-5 w-5"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fillRule="evenodd"
                                            d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z"
                                            clipRule="evenodd"
                                        />
                                        <path d="M15.171 11.586a4 4 0 111.414-1.414l-1.414 1.414zm-1.414-1.414L13.757 12a2 2 0 01-2-2l1.414-1.414a4 4 0 000 5.656z" />
                                    </svg>
                                )}
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
                            className="block text-sm font-medium text-gray-700"
                        >
                            Confirmar Contraseña
                        </label>
                        <div className="mt-1 relative">
                            <input
                                id="password_confirmation"
                                type={showConfirm ? 'text' : 'password'}
                                name="password_confirmation"
                                value={data.password_confirmation}
                                onChange={(e) =>
                                    setData('password_confirmation', e.target.value)
                                }
                                className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 pr-10 ${
                                    errors.password_confirmation
                                        ? 'border-red-500'
                                        : 'border-gray-300'
                                }`}
                                placeholder="Repite tu contraseña"
                                disabled={false}
                                autoComplete="new-password"
                            />
                            <button
                                type="button"
                                onClick={() => setShowConfirm(!showConfirm)}
                                className="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600"
                            >
                                {showConfirm ? (
                                    <svg
                                        className="h-5 w-5"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path
                                            fillRule="evenodd"
                                            d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                            clipRule="evenodd"
                                        />
                                    </svg>
                                ) : (
                                    <svg
                                        className="h-5 w-5"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fillRule="evenodd"
                                            d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z"
                                            clipRule="evenodd"
                                        />
                                        <path d="M15.171 11.586a4 4 0 111.414-1.414l-1.414 1.414zm-1.414-1.414L13.757 12a2 2 0 01-2-2l1.414-1.414a4 4 0 000 5.656z" />
                                    </svg>
                                )}
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
