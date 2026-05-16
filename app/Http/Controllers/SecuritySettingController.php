<?php

namespace App\Http\Controllers;

use App\Actions\UpdateSecuritySetting;
use App\Http\Requests\UpdateSecuritySettingRequest;
use App\Models\SecuritySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecuritySettingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $setting = $this->setting($request);

        abort_unless($request->user()?->hasPermission('security_settings.view', $setting->company_id), 403);

        return response()->json(['data' => $setting->toArray()]);
    }

    public function show(Request $request, SecuritySetting $securitySetting): JsonResponse
    {
        abort_unless(
            $request->user()?->company_id === $securitySetting->company_id
            && $request->user()?->hasPermission('security_settings.view', $securitySetting->company_id),
            403
        );

        return response()->json(['data' => $securitySetting->toArray()]);
    }

    public function update(UpdateSecuritySettingRequest $request, UpdateSecuritySetting $action): JsonResponse
    {
        $setting = $this->setting($request);

        return response()->json(['data' => $action->handle($setting, $request->validated(), $request->user())->toArray()]);
    }

    private function setting(Request $request): SecuritySetting
    {
        $companyId = $request->user()?->company_id;

        abort_if($companyId === null, 403);

        return SecuritySetting::query()->firstOrCreate(['company_id' => $companyId]);
    }
}
