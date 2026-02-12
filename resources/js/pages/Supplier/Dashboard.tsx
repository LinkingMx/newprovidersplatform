import { usePage } from '@inertiajs/react';

interface Supplier {
    id: number;
    name: string;
    email: string;
    status: 'registered' | 'profile_completed' | 'active';
    address_street: string | null;
    address_city: string | null;
    clabe_interbancaria: string | null;
    branches: Array<{ id: number; name: string; created_at: string }>;
    created_at: string;
}

const statusConfig = {
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
        <div className="min-h-screen bg-gray-50 py-12 px-4">
            <div className="max-w-4xl mx-auto">
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
                <div className={`${config.color} rounded-lg shadow p-6 mb-8`}>
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
                            className={`${config.badge} px-4 py-2 rounded-full font-semibold text-sm`}
                        >
                            {config.label}
                        </div>
                    </div>

                    {/* Progress Bar */}
                    <div className="mt-4">
                        <div className="flex items-center justify-between mb-2">
                            <span
                                className={`text-xs font-medium ${config.textColor}`}
                            >
                                Progreso de activación
                            </span>
                            <span
                                className={`text-xs font-medium ${config.textColor}`}
                            >
                                {supplier.status === 'registered'
                                    ? '33%'
                                    : supplier.status === 'profile_completed'
                                      ? '67%'
                                      : '100%'}
                            </span>
                        </div>
                        <div className="w-full bg-gray-200 rounded-full h-2">
                            <div
                                className={`h-2 rounded-full transition-all ${
                                    supplier.status === 'registered'
                                        ? 'bg-blue-500 w-1/3'
                                        : supplier.status === 'profile_completed'
                                          ? 'bg-yellow-500 w-2/3'
                                          : 'bg-green-500 w-full'
                                }`}
                            />
                        </div>
                    </div>

                    {/* CTA Button */}
                    {supplier.status === 'registered' && (
                        <a
                            href={route('supplier.onboarding')}
                            className="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition"
                        >
                            Completar perfil
                        </a>
                    )}
                </div>

                {/* Content Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {/* Personal Information */}
                    <div className="bg-white rounded-lg shadow p-6">
                        <h3 className="text-lg font-semibold text-gray-900 mb-4">
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
                                        {maskClabe(supplier.clabe_interbancaria)}
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Branches */}
                    <div className="bg-white rounded-lg shadow p-6">
                        <h3 className="text-lg font-semibold text-gray-900 mb-4">
                            Mis Sucursales
                        </h3>
                        {supplier.branches && supplier.branches.length > 0 ? (
                            <div className="space-y-3">
                                {supplier.branches.map((branch) => (
                                    <div
                                        key={branch.id}
                                        className="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                                    >
                                        <div>
                                            <p className="font-medium text-gray-900">
                                                {branch.name}
                                            </p>
                                            <p className="text-xs text-gray-500">
                                                Asignada:{' '}
                                                {new Date(
                                                    branch.created_at
                                                ).toLocaleDateString('es-MX')}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-6">
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
                <div className="mt-8 bg-white rounded-lg shadow p-6">
                    <h3 className="text-lg font-semibold text-gray-900 mb-4">
                        Acciones
                    </h3>
                    <div className="flex flex-col gap-2">
                        <a
                            href={route('supplier.logout')}
                            method="post"
                            as="button"
                            className="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg font-medium transition text-left"
                        >
                            Cerrar Sesión
                        </a>
                    </div>
                </div>

                {/* Help Section */}
                <div className="mt-8 bg-blue-50 rounded-lg p-6 border border-blue-200">
                    <h3 className="text-lg font-semibold text-blue-900 mb-2">
                        ¿Necesitas ayuda?
                    </h3>
                    <p className="text-sm text-blue-800 mb-4">
                        Si tienes dudas o necesitas asistencia, contáctanos en:
                    </p>
                    <ul className="text-sm text-blue-800 space-y-2">
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
