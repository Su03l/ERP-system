<?php

namespace App\Http\Controllers;

use App\Actions\CancelSubscriptionInvoice;
use App\Actions\GenerateSubscriptionInvoice;
use App\Actions\MarkSubscriptionInvoicePaid;
use App\Actions\UpdateSubscriptionInvoice;
use App\Http\Requests\IndexSubscriptionInvoiceRequest;
use App\Http\Requests\StoreSubscriptionInvoiceRequest;
use App\Http\Requests\UpdateSubscriptionInvoiceRequest;
use App\Http\Resources\SubscriptionInvoiceResource;
use App\Models\CompanySubscription;
use App\Models\SubscriptionInvoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class SubscriptionInvoiceController extends Controller
{
    public function index(IndexSubscriptionInvoiceRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', SubscriptionInvoice::class);
        $filters = $request->validated();

        $invoices = SubscriptionInvoice::query()
            ->with(['company', 'subscription.plan'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['company_id'] ?? null, fn ($query, int $companyId) => $query->where('company_id', $companyId))
            ->when($filters['subscription_id'] ?? null, fn ($query, int $subscriptionId) => $query->where('subscription_id', $subscriptionId))
            ->when($filters['invoice_date_from'] ?? null, fn ($query, string $date) => $query->whereDate('invoice_date', '>=', $date))
            ->when($filters['invoice_date_until'] ?? null, fn ($query, string $date) => $query->whereDate('invoice_date', '<=', $date))
            ->when($filters['due_date_from'] ?? null, fn ($query, string $date) => $query->whereDate('due_date', '>=', $date))
            ->when($filters['due_date_until'] ?? null, fn ($query, string $date) => $query->whereDate('due_date', '<=', $date))
            ->latest('id')
            ->paginate();

        return SubscriptionInvoiceResource::collection($invoices);
    }

    public function store(StoreSubscriptionInvoiceRequest $request, GenerateSubscriptionInvoice $action): SubscriptionInvoiceResource
    {
        Gate::authorize('generate', SubscriptionInvoice::class);
        $data = $request->validated();
        $subscription = CompanySubscription::query()
            ->where('company_id', (int) $data['company_id'])
            ->findOrFail((int) $data['subscription_id']);

        return SubscriptionInvoiceResource::make($action->handle($subscription, $data, $request->user()));
    }

    public function show(SubscriptionInvoice $subscriptionInvoice): SubscriptionInvoiceResource
    {
        Gate::authorize('view', $subscriptionInvoice);

        return SubscriptionInvoiceResource::make($subscriptionInvoice->load(['company', 'subscription.plan']));
    }

    public function update(UpdateSubscriptionInvoiceRequest $request, SubscriptionInvoice $subscriptionInvoice, UpdateSubscriptionInvoice $action): SubscriptionInvoiceResource
    {
        Gate::authorize('update', $subscriptionInvoice);

        return SubscriptionInvoiceResource::make($action->handle($subscriptionInvoice, $request->validated(), $request->user()));
    }

    public function destroy(SubscriptionInvoice $subscriptionInvoice): never
    {
        abort(405);
    }

    public function markPaid(UpdateSubscriptionInvoiceRequest $request, SubscriptionInvoice $subscriptionInvoice, MarkSubscriptionInvoicePaid $action): SubscriptionInvoiceResource
    {
        Gate::authorize('markPaid', $subscriptionInvoice);

        return SubscriptionInvoiceResource::make($action->handle($subscriptionInvoice, $request->validated(), $request->user()));
    }

    public function cancel(Request $request, SubscriptionInvoice $subscriptionInvoice, CancelSubscriptionInvoice $action): SubscriptionInvoiceResource
    {
        Gate::authorize('update', $subscriptionInvoice);

        return SubscriptionInvoiceResource::make($action->handle($subscriptionInvoice, $request->user(), $request->string('reason')->toString()));
    }
}
