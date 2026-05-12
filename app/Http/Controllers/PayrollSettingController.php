<?php

namespace App\Http\Controllers;

use App\Actions\UpdatePayrollSetting;
use App\Http\Requests\UpdatePayrollSettingRequest;
use App\Http\Resources\PayrollSettingResource;
use App\Models\PayrollSetting;
use Illuminate\Support\Facades\Gate;

class PayrollSettingController extends Controller
{
    public function index(): PayrollSettingResource
    {
        Gate::authorize('viewAny', PayrollSetting::class);

        $setting = PayrollSetting::query()->forCurrentCompany()->firstOrFail();

        return PayrollSettingResource::make($setting);
    }

    public function show(PayrollSetting $payrollSetting): PayrollSettingResource
    {
        Gate::authorize('view', $payrollSetting);

        return PayrollSettingResource::make($payrollSetting);
    }

    public function update(UpdatePayrollSettingRequest $request, PayrollSetting $payrollSetting, UpdatePayrollSetting $action): PayrollSettingResource
    {
        return PayrollSettingResource::make($action->handle($payrollSetting, $request->validated(), $request->user()));
    }
}
