import { usePage, Link } from '@inertiajs/react';

interface Supplier {
    id: number;
    name: string;
    email: string;
    status:
        | 'created'
        | 'invited'
        | 'registered'
        | 'profile_completed'
        | 'active';
    address_street: string | null;
    address_city: string | null;
    clabe_interbancaria: string | null;
    branches: Array<{ id: number; name: string; created_at: string }>;
    created_at: string;
}

const statusConfig = {
    created: {
        color: 'bg-gray-100',
        textColor: 'text-gray-900',
        badge: 'bg-gray-200 text-gray-800',
        label: 'Creado',
        description: 'Tu cuenta ha sido creada. Establece tu contraseña.',
    },
    invited: {
        color: 'bg-purple-100',
        textColor: 'text-purple-900',
        badge: 'bg-purple-200 text-purple-800',
        label: 'Invitado',
        description: 'Completa el registro para activar tu cuenta.',
    },
    registered: {
        color: 'bg-blue-100',
        textColor: 'text-blue-900',
        badge: 'bg-blue-200 text-blue-800',
        label: 'Registrado',
        description: 'Completa tu perfil para activar tu cuenta',
    },
    profile_completed: {
        color: 'bg-yellow-100',
        textColor: 'text-yellow-900',
        badge: 'bg-yellow-200 text-yellow-800',
        label: 'Verificando',
        description: 'Estamos verificando tu información',
    },
    active: {
        color: 'bg-green-100',
        textColor: 'text-green-900',
        badge: 'bg-green-200 text-green-800',
        label: 'Activo',
        description: '¡Tu cuenta está lista para usar!',
    },
};

export default function Dashboard() {
    const { supplier: supplierProp } = usePage().props;
    const supplier = supplierProp as Supplier;
    const config = statusConfig[supplier.status];

    const maskClabe = (clabe: string | null) => {
        if (!clabe) return '••••••••••••••••';
        return clabe.slice(0, -4).replace(/./g, '*') + clabe.slice(-4);
    };

    return (
        <div className="min-h-screen bg-gray-50 px-4 py-12">
            <div className="mx-auto max-w-4xl">
                {/* Header */}
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-gray-900">
                        Mi Cuenta
                    </h1>
                    <p className="mt-2 text-gray-600">
                        Bienvenido, {supplier.name}
                    </p>
                </div>

                {/* Status Card */}
                <div className={`${config.color} mb-8 rounded-lg p-6 shadow`}>
                    <div className="flex items-center justify-between">
                        <div>
                            <h2
                                className={`text-lg font-semibold ${config.textColor}`}
                            >
                                Estado de tu cuenta
                            </h2>
                            <p className={`mt-1 text-sm ${config.textColor}`}>
                                {config.description}
                            </p>
                        </div>
                        <div
                            className={`${config.badge} rounded-full px-4 py-2 text-sm font-semibold`}
                        >
                            {config.label}
                        </div>
                    </div>

                    {/* Progress Bar */}
                    <div className="mt-4">
                        <div className="mb-2 flex items-center justify-between">
                            <span
                                className={`text-xs font-medium ${config.textColor}`}
                            >
                                Progreso de activación
                            </span>
                            <span
                                className={`text-xs font-medium ${config.textColor}`}
                            >
                                {supplier.status === 'created' ||
                                supplier.status === 'invited'
                                    ? '0%'
                                    : supplier.status === 'registered'
                                      ? '33%'
                                      : supplier.status === 'profile_completed'
                                        ? '67%'
                                        : '100%'}
                            </span>
                        </div>
                        <div className="h-2 w-full rounded-full bg-gray-200">
                            <div
                                className={`h-2 rounded-full transition-all ${
                                    supplier.status === 'created' ||
                                    supplier.status === 'invited'
                                        ? 'w-0 bg-gray-500'
                                        : supplier.status === 'registered'
                                          ? 'w-1/3 bg-blue-500'
                                          : supplier.status ===
                                              'profile_completed'
                                            ? 'w-2/3 bg-yellow-500'
                                            : 'w-full bg-green-500'
                                }`}
                            />
                        </div>
                    </div>

                    {/* CTA Button */}
                    {supplier.status === 'registered' && (
                        <a
                            href="/supplier/onboarding"
                            className="mt-4 inline-block rounded-lg bg-blue-600 px-4 py-2 font-medium text-white transition hover:bg-blue-700"
                        >
                            Completar perfil
                        </a>
                    )}
                </div>

                {/* Content Grid */}
                <div className="grid grid-cols-1 gap-8 md:grid-cols-2">
                    {/* Personal Information */}
                    <div className="rounded-lg bg-white p-6 shadow">
                        <h3 className="mb-4 text-lg font-semibold text-gray-900">
                            Información Personal
                        </h3>
                        <div className="space-y-4">
                            <div>
                                <label className="block text-xs font-medium text-gray-500 uppercase">
                                    Nombre
                                </label>
                                <p className="mt-1 text-gray-900">
                                    {supplier.name}
                                </p>
                            </div>
                            <div>
                                <label className="block text-xs font-medium text-gray-500 uppercase">
                                    Email
                                </label>
                                <p className="mt-1 text-gray-900">
                                    {supplier.email}
                                </p>
                            </div>
                            {supplier.address_city && (
                                <div>
                                    <label className="block text-xs font-medium text-gray-500 uppercase">
                                        Ubicación
                                    </label>
                                    <p className="mt-1 text-gray-900">
                                        {supplier.address_street},{' '}
                                        {supplier.address_city}
                                    </p>
                                </div>
                            )}
                            {supplier.clabe_interbancaria && (
                                <div>
                                    <label className="block text-xs font-medium text-gray-500 uppercase">
                                        CLABE
                                    </label>
                                    <p className="mt-1 font-mono text-gray-900">
                                        {maskClabe(
                                            supplier.clabe_interbancaria,
                                        )}
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Branches */}
                    <div className="rounded-lg bg-white p-6 shadow">
                        <h3 className="mb-4 text-lg font-semibold text-gray-900">
                            Mis Sucursales
                        </h3>
                        {supplier.branches && supplier.branches.length > 0 ? (
                            <div className="space-y-3">
                                {supplier.branches.map((branch) => (
                                    <div
                                        key={branch.id}
                                        className="flex items-center justify-between rounded-lg bg-gray-50 p-3"
                                    >
                                        <div>
                                            <p className="font-medium text-gray-900">
                                                {branch.name}
                                            </p>
                                            <p className="text-xs text-gray-500">
                                                Asignada:{' '}
                                                {new Date(
                                                    branch.created_at,
                                                ).toLocaleDateString('es-MX')}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="py-6 text-center">
                                <svg
                                    className="mx-auto h-12 w-12 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                                    />
                                </svg>
                                <p className="mt-2 text-sm text-gray-500">
                                    No hay sucursales asignadas aún
                                </p>
                            </div>
                        )}
                    </div>
                </div>

                {/* Actions */}
                <div className="mt-8 rounded-lg bg-white p-6 shadow">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">
                        Acciones
                    </h3>
                    <div className="flex flex-col gap-2">
                        <Link
                            href="/supplier/auth/logout"
                            method="post"
                            as="button"
                            className="rounded-lg px-4 py-2 text-left font-medium text-red-600 transition hover:bg-red-50"
                        >
                            Cerrar Sesión
                        </Link>
                    </div>
                </div>

                {/* Help Section */}
                <div className="mt-8 rounded-lg border border-blue-200 bg-blue-50 p-6">
                    <h3 className="mb-2 text-lg font-semibold text-blue-900">
                        ¿Necesitas ayuda?
                    </h3>
                    <p className="mb-4 text-sm text-blue-800">
                        Si tienes dudas o necesitas asistencia, contáctanos en:
                    </p>
                    <ul className="space-y-2 text-sm text-blue-800">
                        <li>
                            📧 Email:{' '}
                            <a
                                href="mailto:support@example.com"
                                className="font-medium hover:underline"
                            >
                                support@example.com
                            </a>
                        </li>
                        <li>
                            📞 WhatsApp:{' '}
                            <a
                                href="https://wa.me/..."
                                className="font-medium hover:underline"
                            >
                                +52 1234567890
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    );
}
