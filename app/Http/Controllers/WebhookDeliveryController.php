<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexWebhookDeliveryRequest;
use App\Models\WebhookDelivery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookDeliveryController extends Controller
{
    public function index(IndexWebhookDeliveryRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $deliveries = WebhookDelivery::query()
            ->forCompany($request->user()->company_id)
            ->when($filters['webhook_endpoint_id'] ?? null, fn (Builder $query, int|string $endpointId): Builder => $query->where('webhook_endpoint_id', $endpointId))
            ->when($filters['event_name'] ?? null, fn (Builder $query, string $eventName): Builder => $query->where('event_name', $eventName))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date))
            ->latest('id')
            ->get()
            ->toArray();

        return response()->json(['data' => $deliveries]);
    }

    public function show(Request $request, WebhookDelivery $webhookDelivery): JsonResponse
    {
        abort_unless($request->user()?->company_id === $webhookDelivery->company_id && $request->user()?->hasPermission('webhooks.view', $webhookDelivery->company_id), 403);

        return response()->json(['data' => $webhookDelivery->toArray()]);
    }
}
