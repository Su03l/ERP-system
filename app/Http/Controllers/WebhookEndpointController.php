<?php

namespace App\Http\Controllers;

use App\Actions\CreateWebhookEndpoint;
use App\Actions\DeleteWebhookEndpoint;
use App\Actions\UpdateWebhookEndpoint;
use App\Http\Requests\StoreWebhookEndpointRequest;
use App\Http\Requests\UpdateWebhookEndpointRequest;
use App\Models\WebhookEndpoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookEndpointController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('webhooks.view', $request->user()?->company_id), 403);

        return response()->json([
            'data' => WebhookEndpoint::query()->forCompany($request->user()->company_id)->latest('id')->get()->toArray(),
        ]);
    }

    public function store(StoreWebhookEndpointRequest $request, CreateWebhookEndpoint $action): JsonResponse
    {
        return response()->json(['data' => $action->handle($request->validated(), $request->user())->toArray()], 201);
    }

    public function show(Request $request, WebhookEndpoint $webhookEndpoint): JsonResponse
    {
        abort_unless($this->canView($request, $webhookEndpoint), 403);

        return response()->json(['data' => $webhookEndpoint->toArray()]);
    }

    public function update(UpdateWebhookEndpointRequest $request, WebhookEndpoint $webhookEndpoint, UpdateWebhookEndpoint $action): JsonResponse
    {
        return response()->json(['data' => $action->handle($webhookEndpoint, $request->validated(), $request->user())->toArray()]);
    }

    public function destroy(Request $request, WebhookEndpoint $webhookEndpoint, DeleteWebhookEndpoint $action): JsonResponse
    {
        $action->handle($webhookEndpoint, $request->user());

        return response()->json(status: 204);
    }

    private function canView(Request $request, WebhookEndpoint $endpoint): bool
    {
        $companyId = $request->user()?->company_id;

        return $companyId !== null && $companyId === $endpoint->company_id && ($request->user()?->hasPermission('webhooks.view', $companyId) ?? false);
    }
}
