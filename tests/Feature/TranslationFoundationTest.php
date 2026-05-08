<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

test('arabic and english backend translation groups are available', function (string $locale) {
    App::setLocale($locale);

    expect(__('common.app_name'))->not->toBe('common.app_name')
        ->and(__('common.modules.hr'))->not->toBe('common.modules.hr')
        ->and(__('auth.labels.preferred_locale'))->not->toBe('auth.labels.preferred_locale')
        ->and(__('company.fields.timezone'))->not->toBe('company.fields.timezone')
        ->and(__('hr.fields.employee_number'))->not->toBe('hr.fields.employee_number');
})->with(['ar', 'en']);

test('validation attributes are localized for current backend fields', function () {
    App::setLocale('ar');

    $validator = Validator::make(['timezone' => 'invalid'], [
        'timezone' => ['required', 'timezone'],
    ]);

    expect($validator->errors()->first('timezone'))->toContain('المنطقة الزمنية');
});
