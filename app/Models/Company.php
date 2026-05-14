<?php

namespace App\Models;

use App\Enums\CompanyModule;
use App\Services\CompanyModuleService;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'legal_name',
    'email',
    'phone',
    'status',
    'subdomain',
    'locale',
    'timezone',
    'currency',
    'settings',
])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the users assigned to this company.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the roles defined for this company.
     *
     * @return HasMany<Role, $this>
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    /**
     * Get the departments defined for this company.
     *
     * @return HasMany<Department, $this>
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Get the job titles defined for this company.
     *
     * @return HasMany<JobTitle, $this>
     */
    public function jobTitles(): HasMany
    {
        return $this->hasMany(JobTitle::class);
    }

    /**
     * Get the employees assigned to this company.
     *
     * @return HasMany<Employee, $this>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get the employee documents owned by this company.
     *
     * @return HasMany<EmployeeDocument, $this>
     */
    public function employeeDocuments(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    /**
     * Get the attendance records owned by this company.
     *
     * @return HasMany<AttendanceRecord, $this>
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Get the leave types configured for this company.
     *
     * @return HasMany<LeaveType, $this>
     */
    public function leaveTypes(): HasMany
    {
        return $this->hasMany(LeaveType::class);
    }

    /**
     * Get the leave balances owned by this company.
     *
     * @return HasMany<LeaveBalance, $this>
     */
    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /**
     * Get the leave requests owned by this company.
     *
     * @return HasMany<LeaveRequest, $this>
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get this company's payroll settings.
     *
     * @return HasOne<PayrollSetting, $this>
     */
    public function payrollSetting(): HasOne
    {
        return $this->hasOne(PayrollSetting::class);
    }

    /**
     * Get this company's accounting settings.
     *
     * @return HasOne<AccountingSetting, $this>
     */
    public function accountingSetting(): HasOne
    {
        return $this->hasOne(AccountingSetting::class);
    }

    /**
     * Get this company's asset management settings.
     *
     * @return HasOne<AssetSetting, $this>
     */
    public function assetSetting(): HasOne
    {
        return $this->hasOne(AssetSetting::class);
    }

    /**
     * Get asset categories owned by this company.
     *
     * @return HasMany<AssetCategory, $this>
     */
    public function assetCategories(): HasMany
    {
        return $this->hasMany(AssetCategory::class);
    }

    /**
     * Get assets owned by this company.
     *
     * @return HasMany<Asset, $this>
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    /**
     * Get asset custody records owned by this company.
     *
     * @return HasMany<AssetCustody, $this>
     */
    public function assetCustodies(): HasMany
    {
        return $this->hasMany(AssetCustody::class);
    }

    /**
     * Get depreciation schedules owned by this company.
     *
     * @return HasMany<DepreciationSchedule, $this>
     */
    public function depreciationSchedules(): HasMany
    {
        return $this->hasMany(DepreciationSchedule::class);
    }

    /**
     * Get this company's document settings.
     *
     * @return HasOne<DocumentSetting, $this>
     */
    public function documentSetting(): HasOne
    {
        return $this->hasOne(DocumentSetting::class);
    }

    /**
     * Get this company's project and CRM settings.
     *
     * @return HasOne<ProjectCrmSetting, $this>
     */
    public function projectCrmSetting(): HasOne
    {
        return $this->hasOne(ProjectCrmSetting::class);
    }

    /**
     * Get company documents owned by this company.
     *
     * @return HasMany<CompanyDocument, $this>
     */
    public function companyDocuments(): HasMany
    {
        return $this->hasMany(CompanyDocument::class);
    }

    /**
     * Get chart of accounts records owned by this company.
     *
     * @return HasMany<Account, $this>
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Get journal entries owned by this company.
     *
     * @return HasMany<JournalEntry, $this>
     */
    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Get journal entry lines owned by this company.
     *
     * @return HasMany<JournalEntryLine, $this>
     */
    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    /**
     * Get customers owned by this company.
     *
     * @return HasMany<Customer, $this>
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get CRM leads owned by this company.
     *
     * @return HasMany<CrmLead, $this>
     */
    public function crmLeads(): HasMany
    {
        return $this->hasMany(CrmLead::class);
    }

    /**
     * Get CRM contacts owned by this company.
     *
     * @return HasMany<CrmContact, $this>
     */
    public function crmContacts(): HasMany
    {
        return $this->hasMany(CrmContact::class);
    }

    /**
     * Get projects owned by this company.
     *
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get project tasks owned by this company.
     *
     * @return HasMany<ProjectTask, $this>
     */
    public function projectTasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    /**
     * Get project time logs owned by this company.
     *
     * @return HasMany<ProjectTimeLog, $this>
     */
    public function projectTimeLogs(): HasMany
    {
        return $this->hasMany(ProjectTimeLog::class);
    }

    /**
     * Get SaaS subscriptions owned by this company.
     *
     * @return HasMany<CompanySubscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(CompanySubscription::class);
    }

    /**
     * Get SaaS platform billing invoices for this company.
     *
     * @return HasMany<SubscriptionInvoice, $this>
     */
    public function subscriptionInvoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    /**
     * Get marketplace add-ons enabled for this company.
     *
     * @return HasMany<CompanyAddOn, $this>
     */
    public function companyAddOns(): HasMany
    {
        return $this->hasMany(CompanyAddOn::class);
    }

    /**
     * Get sales invoices owned by this company.
     *
     * @return HasMany<SalesInvoice, $this>
     */
    public function salesInvoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class);
    }

    /**
     * Get sales invoice lines owned by this company.
     *
     * @return HasMany<SalesInvoiceLine, $this>
     */
    public function salesInvoiceLines(): HasMany
    {
        return $this->hasMany(SalesInvoiceLine::class);
    }

    /**
     * Get vendors owned by this company.
     *
     * @return HasMany<Vendor, $this>
     */
    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }

    /** @return HasMany<PurchaseInvoice, $this> */
    public function purchaseInvoices(): HasMany
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    /** @return HasMany<PurchaseInvoiceLine, $this> */
    public function purchaseInvoiceLines(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceLine::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the salary components configured for this company.
     *
     * @return HasMany<SalaryComponent, $this>
     */
    public function salaryComponents(): HasMany
    {
        return $this->hasMany(SalaryComponent::class);
    }

    /**
     * Get the employee salary packages owned by this company.
     *
     * @return HasMany<EmployeeSalaryPackage, $this>
     */
    public function employeeSalaryPackages(): HasMany
    {
        return $this->hasMany(EmployeeSalaryPackage::class);
    }

    /**
     * Get the salary package items owned by this company.
     *
     * @return HasMany<EmployeeSalaryPackageItem, $this>
     */
    public function employeeSalaryPackageItems(): HasMany
    {
        return $this->hasMany(EmployeeSalaryPackageItem::class);
    }

    /**
     * Get payroll periods owned by this company.
     *
     * @return HasMany<PayrollPeriod, $this>
     */
    public function payrollPeriods(): HasMany
    {
        return $this->hasMany(PayrollPeriod::class);
    }

    /**
     * Get payroll runs owned by this company.
     *
     * @return HasMany<PayrollRun, $this>
     */
    public function payrollRuns(): HasMany
    {
        return $this->hasMany(PayrollRun::class);
    }

    /**
     * Get payroll run items owned by this company.
     *
     * @return HasMany<PayrollRunItem, $this>
     */
    public function payrollRunItems(): HasMany
    {
        return $this->hasMany(PayrollRunItem::class);
    }

    /**
     * @return HasMany<Workflow, $this>
     */
    public function workflows(): HasMany
    {
        return $this->hasMany(Workflow::class);
    }

    /**
     * @return HasMany<ImportJob, $this>
     */
    public function importJobs(): HasMany
    {
        return $this->hasMany(ImportJob::class);
    }

    /**
     * @return HasMany<ExportJob, $this>
     */
    public function exportJobs(): HasMany
    {
        return $this->hasMany(ExportJob::class);
    }

    /**
     * @return HasMany<MigrationSession, $this>
     */
    public function migrationSessions(): HasMany
    {
        return $this->hasMany(MigrationSession::class);
    }

    public function hasModule(CompanyModule|string $module): bool
    {
        return app(CompanyModuleService::class)->isEnabled($this, $module);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }
}
