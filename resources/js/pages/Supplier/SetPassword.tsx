import { FormEvent, useState } from 'react';

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
                // Handle different error statuses
                if (response.status === 422) {
                    // Validation error
                    setErrors(
                        data.errors || { password: 'Validación fallida' },
                    );
                } else if (response.status === 419) {
                    // CSRF token error - refresh and retry
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

            // Success - redirect to onboarding or dashboard
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
            <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 p-4">
                <div className="w-full max-w-md rounded-lg bg-white p-8 shadow-lg">
                    <div className="text-center">
                        <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
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
                            Por favor, solicita una nueva invitación al
                            administrador.
                        </p>
                        <a
                            href="/"
                            className="mt-6 inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
                        >
                            Volver al inicio
                        </a>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 p-4">
            <div className="w-full max-w-md rounded-lg bg-white p-8 shadow-lg">
                <div className="mb-8 text-center">
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
                            className="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Contraseña
                        </label>
                        <div className="flex gap-2">
                            <input
                                id="password"
                                type={showPassword ? 'text' : 'password'}
                                name="password"
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                className={`flex-1 rounded-lg border px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none ${
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
                                className="rounded-lg border border-gray-300 px-3 py-2 hover:bg-gray-50"
                                tabIndex={-1}
                            >
                                {showPassword ? '👁️' : '👁️‍🗨️'}
                            </button>
                        </div>
                        {errors.password && (
                            <p className="mt-1 text-sm text-red-600">
                                {Array.isArray(errors.password)
                                    ? errors.password[0]
                                    : errors.password}
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
                            className="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Confirmar Contraseña
                        </label>
                        <div className="flex gap-2">
                            <input
                                id="password_confirmation"
                                type={showConfirm ? 'text' : 'password'}
                                name="password_confirmation"
                                value={passwordConfirmation}
                                onChange={(e) =>
                                    setPasswordConfirmation(e.target.value)
                                }
                                className={`flex-1 rounded-lg border px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none ${
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
                                className="rounded-lg border border-gray-300 px-3 py-2 hover:bg-gray-50"
                                tabIndex={-1}
                            >
                                {showConfirm ? '👁️' : '👁️‍🗨️'}
                            </button>
                        </div>
                        {errors.password_confirmation && (
                            <p className="mt-1 text-sm text-red-600">
                                {Array.isArray(errors.password_confirmation)
                                    ? errors.password_confirmation[0]
                                    : errors.password_confirmation}
                            </p>
                        )}
                    </div>

                    {/* Submit Button */}
                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full rounded-lg bg-indigo-600 px-4 py-2 font-medium text-white transition duration-200 hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
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
