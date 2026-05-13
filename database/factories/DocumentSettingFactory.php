<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\DocumentSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentSetting>
 */
class DocumentSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'default_expiry_reminder_days' => 30,
            'allowed_file_types' => ['pdf', 'jpg', 'png'],
            'max_file_size' => 10240,
            'document_approval_required' => true,
            'metadata' => [],
        ];
    }
}
