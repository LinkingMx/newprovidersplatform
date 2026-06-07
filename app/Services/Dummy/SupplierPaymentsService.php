<?php

declare(strict_types=1);

namespace App\Services\Dummy;

use App\Models\Supplier;
use Carbon\CarbonImmutable;

/**
 * Genera pagos dummy determinísticos por proveedor + sucursal.
 *
 * Reemplazar por integración real SAP B1 (OINV / OVPM) cuando esté lista.
 * Estructura inspirada en OVPM (Outgoing Payments) y OVPM3 (facturas asociadas).
 */
class SupplierPaymentsService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function forSupplier(Supplier $supplier): array
    {
        $start = CarbonImmutable::create(2026, 1, 1, 0, 0, 0);
        $today = CarbonImmutable::now();

        $branches = $supplier->branches;

        if ($branches->isEmpty()) {
            return [];
        }

        $payments = [];
        $folio = 1000;

        foreach ($branches as $branch) {
            // Seed determinístico — mismo proveedor + sucursal = mismo set de pagos
            $seed = crc32("{$supplier->id}-{$branch->id}");
            mt_srand($seed);

            $paymentCount = mt_rand(2, 6);

            for ($i = 0; $i < $paymentCount; $i++) {
                $daysOffset = mt_rand(0, max(1, (int) $start->diffInDays($today)));
                $fecha = $start->addDays($daysOffset);

                if ($fecha->greaterThan($today)) {
                    continue;
                }

                $numFacturas = mt_rand(1, 5);
                $facturas = [];
                $total = 0.0;

                for ($f = 0; $f < $numFacturas; $f++) {
                    $monto = round(mt_rand(500000, 8500000) / 100, 2); // entre $5,000 y $85,000
                    $facturas[] = [
                        'folio' => 'F-'.str_pad((string) ($folio + $f * 7), 6, '0', STR_PAD_LEFT),
                        'fecha_emision' => $fecha->subDays(mt_rand(3, 25))->toDateString(),
                        'concepto' => $this->concepto((int) ($seed + $f)),
                        'subtotal' => round($monto / 1.16, 2),
                        'iva' => round($monto - ($monto / 1.16), 2),
                        'total' => $monto,
                    ];
                    $total += $monto;
                }

                $metodos = ['Transferencia SPEI', 'Transferencia Interbancaria', 'Cheque'];
                $payments[] = [
                    'id' => "{$supplier->id}-{$branch->id}-{$i}",
                    'folio' => 'OVPM-'.str_pad((string) ($folio + $i), 6, '0', STR_PAD_LEFT),
                    'fecha_pago' => $fecha->toDateString(),
                    'branch' => [
                        'id' => $branch->id,
                        'name' => $branch->name,
                    ],
                    'numero_facturas' => $numFacturas,
                    'monto' => round($total, 2),
                    'metodo_pago' => $metodos[mt_rand(0, 2)],
                    'cuenta_destino' => $this->maskClabe($supplier->clabe_interbancaria),
                    'banco_destino' => 'BBVA México',
                    'referencia' => 'REF-'.strtoupper(substr(md5("{$seed}-{$i}"), 0, 10)),
                    'facturas' => $facturas,
                ];

                $folio += 13;
            }
        }

        // Orden descendente por fecha
        usort($payments, fn ($a, $b) => strcmp($b['fecha_pago'], $a['fecha_pago']));

        mt_srand();

        return $payments;
    }

    private function concepto(int $seed): string
    {
        $opts = [
            'Suministro de insumos',
            'Servicios de mantenimiento',
            'Renta de equipo',
            'Productos perecederos',
            'Servicios profesionales',
            'Mercancía general',
            'Insumos de limpieza',
            'Productos cárnicos',
        ];

        return $opts[$seed % count($opts)];
    }

    private function maskClabe(?string $clabe): string
    {
        if (! $clabe || strlen($clabe) < 4) {
            return '•••• •••• •••• •••• ••';
        }

        return '•••• •••• •••• •••• '.substr($clabe, -2);
    }
}
