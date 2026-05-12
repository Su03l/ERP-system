<?php

namespace App\Actions;

use App\Enums\InvoiceStatus;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\SalesInvoiceCalculationService;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CreateSalesInvoice
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly SalesInvoiceCalculationService $calculationService,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?User $actor = null): SalesInvoice
    {
        $actor = $this->actor($actor);
        Gate::forUser($actor)->authorize('sales_invoices.create');
        $companyId = $this->companyId($actor);
        $lines = $data['lines'] ?? [];
        unset($data['lines'], $data['company_id'], $data['subtotal'], $data['tax_amount'], $data['discount_amount'], $data['total_amount'], $data['balance_due']);
        $calculation = $this->calculationService->calculate($lines, $data['paid_amount'] ?? 0);

        return DB::transaction(function () use ($actor, $calculation, $companyId, $data): SalesInvoice {
            $invoice = SalesInvoice::create([
                ...$data,
                ...collect($calculation)->except('lines')->all(),
                'company_id' => $companyId,
                'status' => $data['status'] ?? InvoiceStatus::Draft,
                'currency' => $data['currency'] ?? 'SAR',
            ]);

            $this->createLines($invoice, $calculation['lines']);

            $this->auditLogger->log('sales_invoice.created', $invoice, newValues: $invoice->load('lines')->attributesToArray(), user: $actor, company: $companyId);

            return $invoice->refresh()->load('lines');
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    private function createLines(SalesInvoice $invoice, array $lines): void
    {
        foreach ($lines as $line) {
            $invoice->lines()->create([
                ...$line,
                'company_id' => $invoice->company_id,
            ]);
        }
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to create sales invoices.');
        }

        return $actor;
    }

    private function companyId(User $actor): int
    {
        $companyId = $this->tenantContext->companyId();

        if ($companyId === null || $actor->company_id !== $companyId) {
            throw new AuthorizationException('A current company is required.');
        }

        return $companyId;
    }
}
