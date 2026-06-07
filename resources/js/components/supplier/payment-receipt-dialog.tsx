import { CheckCircle2 } from 'lucide-react';
import BrandLogo from '@/components/brand-logo';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { Payment } from '@/types/supplier';

interface Props {
    payment: Payment | null;
    supplierName: string;
    supplierRfc: string | null;
    onClose: () => void;
}

function formatMoney(n: number): string {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(n);
}

function formatDate(iso: string): string {
    return new Date(iso + 'T00:00:00').toLocaleDateString('es-MX', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
}

const UNITS = [
    '',
    'UNO',
    'DOS',
    'TRES',
    'CUATRO',
    'CINCO',
    'SEIS',
    'SIETE',
    'OCHO',
    'NUEVE',
];
const TEENS = [
    'DIEZ',
    'ONCE',
    'DOCE',
    'TRECE',
    'CATORCE',
    'QUINCE',
    'DIECISÉIS',
    'DIECISIETE',
    'DIECIOCHO',
    'DIECINUEVE',
];
const TENS = [
    '',
    '',
    'VEINTE',
    'TREINTA',
    'CUARENTA',
    'CINCUENTA',
    'SESENTA',
    'SETENTA',
    'OCHENTA',
    'NOVENTA',
];
const HUNDREDS = [
    '',
    'CIENTO',
    'DOSCIENTOS',
    'TRESCIENTOS',
    'CUATROCIENTOS',
    'QUINIENTOS',
    'SEISCIENTOS',
    'SETECIENTOS',
    'OCHOCIENTOS',
    'NOVECIENTOS',
];

function numberToSpanish(n: number): string {
    if (n === 0) return 'CERO';
    if (n === 100) return 'CIEN';

    let result = '';

    if (n >= 1000000) {
        const millions = Math.floor(n / 1000000);
        result +=
            millions === 1
                ? 'UN MILLÓN '
                : numberToSpanish(millions) + ' MILLONES ';
        n = n % 1000000;
    }

    if (n >= 1000) {
        const thousands = Math.floor(n / 1000);
        result +=
            thousands === 1 ? 'MIL ' : numberToSpanish(thousands) + ' MIL ';
        n = n % 1000;
    }

    if (n >= 100) {
        result += HUNDREDS[Math.floor(n / 100)] + ' ';
        n = n % 100;
    }

    if (n >= 20) {
        result += TENS[Math.floor(n / 10)];
        if (n % 10 > 0) result += ' Y ' + UNITS[n % 10];
        result += ' ';
    } else if (n >= 10) {
        result += TEENS[n - 10] + ' ';
    } else if (n > 0) {
        result += UNITS[n] + ' ';
    }

    return result.trim();
}

function moneyToWords(amount: number): string {
    const entero = Math.floor(amount);
    const cents = Math.round((amount - entero) * 100);
    const centsStr = cents.toString().padStart(2, '0');
    return `${numberToSpanish(entero)} PESOS ${centsStr}/100 M.N.`;
}

export default function PaymentReceiptDialog({
    payment,
    supplierName,
    supplierRfc,
    onClose,
}: Props) {
    return (
        <Dialog open={!!payment} onOpenChange={(open) => !open && onClose()}>
            <DialogContent className="gap-0 overflow-hidden p-0 sm:max-w-3xl lg:max-w-4xl">
                {payment && (
                    <>
                        <DialogHeader className="border-b bg-muted/30 px-6 py-4 pr-12">
                            <div className="flex items-center justify-between gap-3">
                                <BrandLogo className="h-8" />
                                <div className="text-right">
                                    <DialogTitle className="text-sm font-semibold tracking-wider uppercase">
                                        Comprobante de Pago
                                    </DialogTitle>
                                    <DialogDescription className="font-mono text-xs">
                                        {payment.folio}
                                    </DialogDescription>
                                </div>
                            </div>
                        </DialogHeader>

                        <div className="max-h-[70vh] space-y-5 overflow-y-auto px-6 py-5">
                            {/* Estado */}
                            <div className="flex items-center justify-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 dark:border-green-900 dark:bg-green-950/40">
                                <CheckCircle2 className="size-5 text-green-600 dark:text-green-400" />
                                <p className="text-sm font-semibold text-green-700 dark:text-green-300">
                                    PAGO APLICADO
                                </p>
                            </div>

                            {/* Info principal */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                        Fecha de pago
                                    </p>
                                    <p className="mt-1 font-medium">
                                        {formatDate(payment.fecha_pago)}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                        Sucursal pagadora
                                    </p>
                                    <p className="mt-1 font-medium">
                                        {payment.branch.name}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                        Beneficiario
                                    </p>
                                    <p className="mt-1 font-medium">
                                        {supplierName}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                        RFC
                                    </p>
                                    <p className="mt-1 font-mono font-medium">
                                        {supplierRfc ?? '—'}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                        Método de pago
                                    </p>
                                    <p className="mt-1 font-medium">
                                        {payment.metodo_pago}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                        Banco destino
                                    </p>
                                    <p className="mt-1 font-medium">
                                        {payment.banco_destino}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                        Cuenta destino
                                    </p>
                                    <p className="mt-1 font-mono text-sm">
                                        {payment.cuenta_destino}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                        Referencia
                                    </p>
                                    <p className="mt-1 font-mono text-sm">
                                        {payment.referencia}
                                    </p>
                                </div>
                            </div>

                            <Separator />

                            {/* Facturas pagadas */}
                            <div>
                                <p className="mb-2 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Facturas aplicadas (
                                    {payment.numero_facturas})
                                </p>
                                <div className="overflow-hidden rounded-md border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow className="bg-muted/40">
                                                <TableHead>Folio</TableHead>
                                                <TableHead>Concepto</TableHead>
                                                <TableHead className="text-right">
                                                    Subtotal
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    IVA
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    Total
                                                </TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {payment.facturas.map((f) => (
                                                <TableRow key={f.folio}>
                                                    <TableCell className="font-mono text-xs">
                                                        {f.folio}
                                                    </TableCell>
                                                    <TableCell className="text-sm">
                                                        {f.concepto}
                                                    </TableCell>
                                                    <TableCell className="text-right text-sm">
                                                        {formatMoney(
                                                            f.subtotal,
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="text-right text-sm">
                                                        {formatMoney(f.iva)}
                                                    </TableCell>
                                                    <TableCell className="text-right text-sm font-medium">
                                                        {formatMoney(f.total)}
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </div>

                            <Separator />

                            {/* Total */}
                            <div className="rounded-lg bg-primary/5 px-4 py-3">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium text-muted-foreground">
                                        TOTAL PAGADO
                                    </span>
                                    <span className="text-2xl font-bold text-foreground">
                                        {formatMoney(payment.monto)}
                                    </span>
                                </div>
                                <p className="mt-1 text-right text-[10px] tracking-wide text-muted-foreground uppercase">
                                    ({moneyToWords(payment.monto)})
                                </p>
                            </div>
                        </div>

                        <DialogFooter className="border-t bg-muted/30 px-6 py-3">
                            <p className="w-full text-center text-[10px] tracking-wider text-muted-foreground uppercase">
                                Documento informativo, no válido como factura
                                fiscal
                            </p>
                        </DialogFooter>
                    </>
                )}
            </DialogContent>
        </Dialog>
    );
}
