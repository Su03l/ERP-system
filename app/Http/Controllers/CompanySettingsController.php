<?php

namespace App\Http\Controllers;

use App\Actions\UpdateSecuritySetting;
use App\Http\Requests\UpdateCompanySettingsRequest;
use App\Http\Requests\UpdateSecuritySettingRequest;
use App\Models\SecuritySetting;
use App\Services\CompanySettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanySettingsController extends Controller
{
    public function __construct(
        protected CompanySettingsService $settingsService,
        protected UpdateSecuritySetting $securityAction
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->hasPermission('company.settings.view', $user->company_id), 403);

        $company = $user->company;
        $settings = $this->settingsService->read($company);

        $securitySetting = SecuritySetting::firstOrCreate(['company_id' => $company->id]);

        return view('settings.index', compact('company', 'settings', 'securitySetting'));
    }

    public function update(UpdateCompanySettingsRequest $request): RedirectResponse
    {
        $user = $request->user();
        $company = $user->company;

        $this->settingsService->update($company, $request->validated());

        return redirect()->route('company-settings.index')->with('success', __('company.settings_updated_successfully'));
    }

    public function updateSecurity(UpdateSecuritySettingRequest $request): RedirectResponse
    {
        $user = $request->user();
        $companyId = $user->company_id;

        $setting = SecuritySetting::firstOrCreate(['company_id' => $companyId]);

        $this->securityAction->handle($setting, $request->validated(), $user);

        return redirect()->route('company-settings.index')->with('success', __('security.settings_updated_successfully'));
    }
}
