<?php

namespace App\Services;

use App\DTOs\WebhookEventDefinition;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Project;
use App\Models\SalesInvoice;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class WebhookEventRegistry
{
    /** @return array<int, WebhookEventDefinition> */
    public function definitions(): array
    {
        return [
            new WebhookEventDefinition('customer.created', 'crm', self::class.'@customerPayload', 'crm'),
            new WebhookEventDefinition('sales_invoice.issued', 'accounting', self::class.'@salesInvoicePayload', 'accounting'),
            new WebhookEventDefinition('invoice.issued', 'accounting', self::class.'@salesInvoicePayload', 'accounting'),
            new WebhookEventDefinition('payment.received', 'accounting', self::class.'@paymentPayload', 'accounting'),
            new WebhookEventDefinition('project.created', 'projects', self::class.'@projectPayload', 'projects'),
            new WebhookEventDefinition('employee.created', 'hr', self::class.'@safeModelPayload', 'hr'),
            new WebhookEventDefinition('document.expiring', 'documents', self::class.'@safeModelPayload', 'documents'),
            new WebhookEventDefinition('payroll.approved', 'payroll', self::class.'@safeModelPayload', 'payroll'),
        ];
    }

    public function definition(string $eventName): WebhookEventDefinition
    {
        foreach ($this->definitions() as $definition) {
            if ($definition->name === $eventName) {
                return $definition;
            }
        }

        throw new InvalidArgumentException("Webhook event [{$eventName}] is not registered.");
    }

    /** @return array<string, mixed> */
    public function payload(string $eventName, Model $model): array
    {
        $method = str($this->definition($eventName)->payloadResolver)->after('@')->toString();

        return $this->{$method}($model);
    }

    /** @return array<string, mixed> */
    public function customerPayload(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'name_ar' => $customer->name_ar,
            'name_en' => $customer->name_en,
            'code' => $customer->code,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'status' => $customer->status?->value,
        ];
    }

    /** @return array<string, mixed> */
    public function salesInvoicePayload(SalesInvoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->invoice_date?->toDateString(),
            'status' => $invoice->status?->value,
            'total_amount' => $invoice->total_amount,
            'balance_due' => $invoice->balance_due,
            'currency' => $invoice->currency,
        ];
    }

    /** @return array<string, mixed> */
    public function paymentPayload(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'payment_number' => $payment->payment_number,
            'payment_date' => $payment->payment_date?->toDateString(),
            'direction' => $payment->direction,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'status' => $payment->status?->value ?? $payment->status,
        ];
    }

    /** @return array<string, mixed> */
    public function projectPayload(Project $project): array
    {
        return [
            'id' => $project->id,
            'code' => $project->code,
            'name_ar' => $project->name_ar,
            'name_en' => $project->name_en,
            'status' => $project->status?->value,
            'priority' => $project->priority?->value,
            'progress_percentage' => $project->progress_percentage,
        ];
    }

    /** @return array<string, mixed> */
    public function safeModelPayload(Model $model): array
    {
        return [
            'id' => $model->getKey(),
            'type' => $model->getMorphClass(),
        ];
    }
}
