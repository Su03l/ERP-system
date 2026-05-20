<?php

namespace App\Http\Controllers;

use App\Actions\UpdatePayrollSetting;
use App\Http\Requests\UpdatePayrollSettingRequest;
use App\Http\Resources\PayrollSettingResource;
use App\Models\PayrollSetting;
use Illuminate\Support\Facades\Gate;

class PayrollSettingController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', PayrollSetting::class);

        $setting = PayrollSetting::query()->forCurrentCompany()->firstOrFail();

        if (request()->expectsJson()) {
            return PayrollSettingResource::make($setting);
        }

        return view('payroll-settings.edit', compact('setting'));
    }

    public function show(PayrollSetting $payrollSetting)
    {
        Gate::authorize('view', $payrollSetting);

        if (request()->expectsJson()) {
            return PayrollSettingResource::make($payrollSetting);
        }

        $setting = $payrollSetting;

        return view('payroll-settings.edit', compact('setting'));
    }

    public function update(UpdatePayrollSettingRequest $request, PayrollSetting $payrollSetting, UpdatePayrollSetting $action)
    {
        $result = $action->handle($payrollSetting, $request->validated(), $request->user());

        if ($request->expectsJson()) {
            return PayrollSettingResource::make($result);
        }

        return redirect()->back()->with('success', app()->getLocale() === 'ar' ? 'تم تحديث إعدادات الرواتب بنجاح.' : 'Payroll settings updated successfully.');
    }
}
