<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BranchRequestStatus;
use App\Models\Branch;
use App\Models\BranchRequest;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BranchRequest> */
class BranchRequestFactory extends Factory
{
    protected $model = BranchRequest::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'branch_id' => Branch::factory(),
            'status' => BranchRequestStatus::Pending,
            'notas_proveedor' => fake()->optional()->sentence(),
            'notas_admin' => null,
            'requested_at' => now(),
            'resolved_at' => null,
            'resolved_by' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => BranchRequestStatus::Approved,
            'resolved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => BranchRequestStatus::Rejected,
            'notas_admin' => fake()->sentence(),
            'resolved_at' => now(),
        ]);
    }
}
