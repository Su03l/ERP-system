<?php

namespace Database\Factories;

use App\Enums\CustodyStatus;
use App\Models\Asset;
use App\Models\AssetCustody;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetCustody>
 */
class AssetCustodyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $company = Company::factory();

        return [
            'company_id' => $company,
            'asset_id' => Asset::factory()->for($company),
            'employee_id' => Employee::factory()->for($company),
            'assigned_by' => null,
            'assigned_at' => null,
            'returned_at' => null,
            'return_received_by' => null,
            'status' => CustodyStatus::Pending,
            'notes_ar' => null,
            'notes_en' => null,
            'workflow_instance_id' => null,
            'metadata' => [],
        ];
    }
}
