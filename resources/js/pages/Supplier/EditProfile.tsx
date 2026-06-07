import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, LoaderCircle } from 'lucide-react';
import type { FormEvent } from 'react';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import SupplierLayout from '@/layouts/supplier/supplier-layout';
import type { Supplier } from '@/types/supplier';

interface Props {
    supplier: Pick<
        Supplier,
        | 'name'
        | 'email'
        | 'rfc'
        | 'address_street'
        | 'address_number'
        | 'address_neighborhood'
        | 'address_city'
        | 'address_country'
        | 'address_zip'
        | 'clabe_interbancaria'
    >;
}

export default function EditProfile({ supplier }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        rfc: supplier.rfc || '',
        address_street: supplier.address_street || '',
        address_number: supplier.address_number || '',
        address_neighborhood: supplier.address_neighborhood || '',
        address_city: supplier.address_city || '',
        address_country: supplier.address_country || 'Mexico',
        address_zip: supplier.address_zip || '',
        clabe_interbancaria: supplier.clabe_interbancaria || '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        put('/supplier/profile');
    };

    return (
        <>
            <Head title="Editar Perfil" />
            <SupplierLayout supplier={supplier as Supplier}>
                <div className="mb-6">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href="/dashboard">
                            <ArrowLeft className="size-4" />
                            Volver al Dashboard
                        </Link>
                    </Button>
                </div>

                <form onSubmit={handleSubmit}>
                    <div className="space-y-6">
                        {/* Identidad Fiscal */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base">
                                    Identidad Fiscal
                                </CardTitle>
                                <CardDescription>
                                    RFC asignado por el SAT. Opcional por ahora.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="grid gap-2">
                                    <Label htmlFor="rfc">RFC</Label>
                                    <Input
                                        id="rfc"
                                        type="text"
                                        value={data.rfc}
                                        onChange={(e) =>
                                            setData(
                                                'rfc',
                                                e.target.value.toUpperCase(),
                                            )
                                        }
                                        placeholder="ABCD010101AB1"
                                        maxLength={13}
                                        className="font-mono uppercase"
                                    />
                                    <InputError message={errors.rfc} />
                                    <p className="text-xs text-muted-foreground">
                                        12 caracteres para persona moral, 13
                                        para persona física.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Address */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base">
                                    Dirección
                                </CardTitle>
                                <CardDescription>
                                    Tu dirección fiscal o de operaciones
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                                        Colonia
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
                                        message={errors.address_neighborhood}
                                    />
                                </div>

                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                                            setData('address_country', value)
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
                            </CardContent>
                        </Card>

                        {/* Banking */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base">
                                    Datos Bancarios
                                </CardTitle>
                                <CardDescription>
                                    Cuenta para recibir pagos
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
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
                            </CardContent>
                        </Card>

                        <Separator />

                        {/* Actions */}
                        <div className="flex justify-end gap-3">
                            <Button variant="outline" asChild>
                                <Link href="/dashboard">Cancelar</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing && (
                                    <LoaderCircle className="size-4 animate-spin" />
                                )}
                                {processing
                                    ? 'Guardando...'
                                    : 'Guardar Cambios'}
                            </Button>
                        </div>
                    </div>
                </form>
            </SupplierLayout>
        </>
    );
}
