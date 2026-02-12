import { FormEvent, useState } from 'react';
import { useForm } from '@inertiajs/react';

interface Props {
    errors?: Record<string, string>;
}

export default function Login({ errors: initialErrors }: Props) {
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
        <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 p-4">
            <div className="w-full max-w-md rounded-lg bg-white p-8 shadow-lg">
                <div className="mb-8 text-center">
                    <h1 className="text-2xl font-bold text-gray-900">
                        Acceso de Proveedores
                    </h1>
                    <p className="mt-2 text-sm text-gray-500">
                        Ingresa con tu correo y contraseña
                    </p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Email Field */}
                    <div>
                        <label
                            htmlFor="email"
                            className="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Correo Electrónico
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            className={`w-full rounded-lg border px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none ${
                                errors.email
                                    ? 'border-red-500'
                                    : 'border-gray-300'
                            }`}
                            placeholder="tu@correo.com"
                            required
                            autoComplete="email"
                        />
                        {errors.email && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.email}
                            </p>
                        )}
                    </div>

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
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                className={`flex-1 rounded-lg border px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none ${
                                    errors.password
                                        ? 'border-red-500'
                                        : 'border-gray-300'
                                }`}
                                placeholder="••••••••"
                                required
                                autoComplete="current-password"
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
                                {errors.password}
                            </p>
                        )}
                    </div>

                    {/* Submit Button */}
                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full rounded-lg bg-indigo-600 px-4 py-2 font-medium text-white transition duration-200 hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {processing ? 'Accediendo...' : 'Acceder'}
                    </button>
                </form>

                <p className="mt-6 text-center text-xs text-gray-500">
                    ¿No tienes cuenta? Espera a tu invitación de registro
                </p>
            </div>
        </div>
    );
}
