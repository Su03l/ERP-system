<?php

namespace App\Actions;

use App\Enums\InvoiceStatus;
use App\Models\PurchaseInvoice;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\PurchaseInvoiceCalculationService;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CreatePurchaseInvoice
{
    public function __construct(private readonly AuditLogger $auditLogger, private readonly PurchaseInvoiceCalculationService $calculationService, private readonly TenantContext $tenantContext) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data, ?User $actor = null): PurchaseInvoice
    {
        $actor = $this->actor($actor);
        Gate::forUser($actor)->authorize('purchase_invoices.create');
        $companyId = $this->companyId($actor);
        $lines = $data['lines'] ?? [];
        unset($data['lines'], $data['company_id'], $data['subtotal'], $data['tax_amount'], $data['discount_amount'], $data['total_amount'], $data['balance_due']);
        $calculation = $this->calculationService->calculate($lines, $data['paid_amount'] ?? 0);

        return DB::transaction(function () use ($actor, $calculation, $companyId, $data): PurchaseInvoice {
            $invoice = PurchaseInvoice::create([
                ...$data,
                ...collect($calculation)->except('lines')->all(),
                'company_id' => $companyId,
                'status' => $data['status'] ?? InvoiceStatus::Draft,
                'currency' => $data['currency'] ?? 'SAR',
            ]);

            foreach ($calculation['lines'] as $line) {
                $invoice->lines()->create([...$line, 'company_id' => $companyId]);
            }

            $this->auditLogger->log('purchase_invoice.created', $invoice, newValues: $invoice->load('lines')->attributesToArray(), user: $actor, company: $companyId);

            return $invoice->refresh()->load('lines');
        });
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to create purchase invoices.');
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
