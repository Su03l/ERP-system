<?php

use App\Enums\CustomerStatus;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Enums\InvoiceStatus;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Company;
use App\Models\CompanyDocument;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Project;
use App\Models\SalesInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // 1. Current Tenant Company
    $this->company = Company::create([
        'name' => 'شركة نوات لتقنية المعلومات',
        'subdomain' => 'nawwat-tech',
        'status' => 'active',
        'locale' => 'ar',
        'settings' => [],
    ]);

    $this->user = User::create([
        'name' => 'بسام عبدالله',
        'email' => 'bassam@nawwat.sa',
        'password' => bcrypt('password123'),
        'company_id' => $this->company->id,
        'preferred_locale' => 'ar',
    ]);

    // 2. Foreign Tenant Company (for isolation check)
    $this->otherCompany = Company::create([
        'name' => 'شركة منافسة محدودة',
        'subdomain' => 'competitor',
        'status' => 'active',
        'locale' => 'ar',
        'settings' => [],
    ]);

    $this->otherUser = User::create([
        'name' => 'شخص آخر',
        'email' => 'other@competitor.sa',
        'password' => bcrypt('password123'),
        'company_id' => $this->otherCompany->id,
        'preferred_locale' => 'ar',
    ]);
});

test('unauthenticated users are redirected from global search endpoint', function () {
    $this->getJson(route('global-search', ['q' => 'test']))
        ->assertStatus(401);
});

test('authenticated search with empty query returns empty grouped keys', function () {
    $this->actingAs($this->user);

    $response = $this->getJson(route('global-search', ['q' => '']));

    $response->assertStatus(200)
        ->assertJson([
            'employees' => [],
            'projects' => [],
            'invoices' => [],
            'customers' => [],
            'documents' => [],
        ]);
});

test('it returns matches for employees, projects, invoices, customers, and documents correctly', function () {
    app()->setLocale('ar');
    $this->actingAs($this->user);

    // Seed Current Company Records
    $employee = Employee::create([
        'company_id' => $this->company->id,
        'first_name_ar' => 'أحمد',
        'last_name_ar' => 'سالم',
        'first_name_en' => 'Ahmed',
        'last_name_en' => 'Salem',
        'email' => 'ahmed.salem@nawwat.sa',
        'employee_number' => 'EMP-1002',
    ]);

    $project = Project::create([
        'company_id' => $this->company->id,
        'name_ar' => 'تطوير بوابة العميل الإدارية',
        'name_en' => 'Customer Portal Development',
        'code' => 'PRJ-PORTAL',
        'status' => ProjectStatus::Draft,
        'priority' => ProjectPriority::Medium,
    ]);

    $invoice = SalesInvoice::create([
        'company_id' => $this->company->id,
        'invoice_number' => 'INV-2026-009',
        'invoice_date' => now(),
        'due_date' => now()->addDays(30),
        'status' => InvoiceStatus::Draft,
        'total_amount' => 12500.00,
        'balance_due' => 12500.00,
    ]);

    $customer = Customer::create([
        'company_id' => $this->company->id,
        'name_ar' => 'شركة الأفق المحدودة',
        'name_en' => 'Horizon Co.',
        'code' => 'CUST-HORIZON',
        'email' => 'info@horizon.sa',
        'status' => CustomerStatus::Active,
    ]);

    $document = CompanyDocument::create([
        'company_id' => $this->company->id,
        'title_ar' => 'سياسة الأمان والخصوصية 2026',
        'title_en' => 'Security and Privacy Policy 2026',
        'document_type' => DocumentType::Policy,
        'status' => DocumentStatus::Active,
        'file_path' => 'docs/security.pdf',
    ]);

    // Search for general term '2026' or 'Horizon' or parts of names
    $response = $this->getJson(route('global-search', ['q' => '2026']));
    $response->assertStatus(200);

    // Document contains 2026 in title
    $response->assertJsonFragment([
        'title' => 'سياسة الأمان والخصوصية 2026',
    ]);

    // Search specifically for 'Ahmed'
    $response = $this->getJson(route('global-search', ['q' => 'Ahmed']));
    $response->assertStatus(200);
    $response->assertJsonFragment([
        'title' => 'أحمد سالم',
        'subtitle' => 'EMP-1002',
    ]);

    // Search specifically for 'PORTAL'
    $response = $this->getJson(route('global-search', ['q' => 'PORTAL']));
    $response->assertStatus(200);
    $response->assertJsonFragment([
        'title' => 'تطوير بوابة العميل الإدارية',
        'subtitle' => 'PRJ-PORTAL',
    ]);

    // Search specifically for 'INV-2026-009'
    $response = $this->getJson(route('global-search', ['q' => 'INV-2026']));
    $response->assertStatus(200);
    $response->assertJsonFragment([
        'title' => 'INV-2026-009',
    ]);
});

test('strict multi-tenant isolation prevents data leakage between companies during global search', function () {
    app()->setLocale('ar');
    // Authenticate as User from current company
    $this->actingAs($this->user);

    // Seed matched keywords in both companies
    // Matcher query = "Alpha"

    // Company A (current) records
    Employee::create([
        'company_id' => $this->company->id,
        'first_name_ar' => 'موظف',
        'last_name_ar' => 'ألفا',
        'first_name_en' => 'Employee',
        'last_name_en' => 'Alpha',
        'email' => 'alpha@nawwat.sa',
        'employee_number' => 'EMP-A',
    ]);

    // Company B (foreign) records - SHOULD NOT be returned
    Employee::create([
        'company_id' => $this->otherCompany->id,
        'first_name_ar' => 'منافس',
        'last_name_ar' => 'ألفا',
        'first_name_en' => 'Competitor',
        'last_name_en' => 'Alpha',
        'email' => 'competitor.alpha@leak.sa',
        'employee_number' => 'EMP-B',
    ]);

    $response = $this->getJson(route('global-search', ['q' => 'Alpha']));

    $response->assertStatus(200);

    // Should see Company A employee
    $response->assertJsonFragment([
        'title' => 'موظف ألفا',
    ]);

    // SHOULD NOT see Company B competitor employee
    $response->assertJsonMissing([
        'title' => 'منافس ألفا',
    ]);
});
