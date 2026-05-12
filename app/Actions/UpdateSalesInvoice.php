<?php

namespace App\Actions;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\SalesInvoiceCalculationService;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class UpdateSalesInvoice
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly SalesInvoiceCalculationService $calculationService,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(SalesInvoice $invoice, array $data, ?User $actor = null): SalesInvoice
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($invoice, $actor);
        Gate::forUser($actor)->authorize('sales_invoices.update');
        $this->ensureEditable($invoice);

        $incomingLines = $data['lines'] ?? null;
        unset($data['lines'], $data['company_id'], $data['subtotal'], $data['tax_amount'], $data['discount_amount'], $data['total_amount'], $data['balance_due']);
        $this->ensureReferencesBelongToCompany($data, $invoice->company_id);
        $lines = is_array($incomingLines) ? $incomingLines : $this->existingLines($invoice);
        $calculation = $this->calculationService->calculate($lines, $data['paid_amount'] ?? $invoice->paid_amount);

        return DB::transaction(function () use ($actor, $calculation, $data, $incomingLines, $invoice): SalesInvoice {
            $invoice->load('lines');
            $oldValues = $invoice->attributesToArray();
            $oldValues['lines'] = $invoice->lines->map->attributesToArray()->all();

            $invoice->update([
                ...$data,
                ...collect($calculation)->except('lines')->all(),
            ]);

            if (is_array($incomingLines)) {
                $invoice->lines()->delete();
                foreach ($calculation['lines'] as $line) {
                    $invoice->lines()->create([...$line, 'company_id' => $invoice->company_id]);
                }
            }

            $this->auditLogger->log('sales_invoice.updated', $invoice, $oldValues, $invoice->refresh()->load('lines')->attributesToArray(), user: $actor, company: $invoice->company_id);

            return $invoice;
        });
    }

    /** @param array<string, mixed> $data */
    private function ensureReferencesBelongToCompany(array $data, int $companyId): void
    {
        if (array_key_exists('customer_id', $data) && $data['customer_id'] !== null && ! Customer::query()->where('company_id', $companyId)->whereKey($data['customer_id'])->exists()) {
            throw new AuthorizationException('Customer does not belong to the current company.');
        }

        if (array_key_exists('posted_journal_entry_id', $data) && $data['posted_journal_entry_id'] !== null && ! JournalEntry::query()->where('company_id', $companyId)->whereKey($data['posted_journal_entry_id'])->exists()) {
            throw new AuthorizationException('Posted journal entry does not belong to the current company.');
        }
    }

    private function ensureEditable(SalesInvoice $invoice): void
    {
        if ($invoice->posted_journal_entry_id !== null || $invoice->status !== InvoiceStatus::Draft) {
            throw ValidationException::withMessages([
                'status' => __('accounting.validation.sales_invoices.editable_status'),
            ]);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function existingLines(SalesInvoice $invoice): array
    {
        return $invoice->lines()->get()->map(fn ($line): array => [
            'description_ar' => $line->description_ar,
            'description_en' => $line->description_en,
            'quantity' => $line->quantity,
            'unit_price' => $line->unit_price,
            'discount_amount' => $line->discount_amount,
            'tax_rate' => $line->tax_rate,
            'metadata' => $line->metadata,
        ])->all();
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to update sales invoices.');
        }

        return $actor;
    }

    private function ensureTenant(SalesInvoice $invoice, User $actor): void
    {
        if ($this->tenantContext->companyId() !== $invoice->company_id || $actor->company_id !== $invoice->company_id) {
            throw new AuthorizationException('Sales invoice does not belong to the current company.');
        }
    }
}
