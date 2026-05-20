<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a default company
        $company = Company::factory()->create([
            'name' => 'الشركة الافتراضية',
        ]);

        // Create a default user
        $user = User::factory()->create([
            'name' => 'مدير النظام',
            'email' => 'admin@nawwat.com',
            'company_id' => $company->id,
        ]);

        // Create some departments
        $departmentsData = [
            ['ar' => 'الإدارة', 'en' => 'Management'],
            ['ar' => 'المبيعات', 'en' => 'Sales'],
            ['ar' => 'التسويق', 'en' => 'Marketing'],
            ['ar' => 'الموارد البشرية', 'en' => 'Human Resources'],
            ['ar' => 'تقنية المعلومات', 'en' => 'IT'],
        ];

        $departments = collect($departmentsData)
            ->map(fn ($data) => Department::factory()->create([
                'name_ar' => $data['ar'],
                'name_en' => $data['en'],
                'company_id' => $company->id,
            ]));

        // Create some job titles
        $jobTitlesData = [
            ['ar' => 'مدير', 'en' => 'Manager'],
            ['ar' => 'موظف', 'en' => 'Employee'],
            ['ar' => 'محاسب', 'en' => 'Accountant'],
            ['ar' => 'مندوب مبيعات', 'en' => 'Sales Rep'],
            ['ar' => 'مسوق الكتروني', 'en' => 'Digital Marketer'],
        ];

        $jobTitles = collect($jobTitlesData)
            ->map(fn ($data) => JobTitle::factory()->create([
                'name_ar' => $data['ar'],
                'name_en' => $data['en'],
                'company_id' => $company->id,
            ]));

        // Create some employees
        Employee::factory(20)->create([
            'company_id' => $company->id,
            'department_id' => fn () => $departments->random()->id,
            'job_title_id' => fn () => $jobTitles->random()->id,
        ]);

        // Call the permissions seeder
        $this->call(PermissionsSeeder::class);
    }
}
