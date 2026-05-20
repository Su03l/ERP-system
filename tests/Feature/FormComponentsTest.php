<?php

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

beforeEach(function () {
    // Set up dummy validation error bags for testing error states
    $errors = new ViewErrorBag;
    $bag = new MessageBag([
        'username' => ['اسم المستخدم مطلوب.'],
        'bio' => ['السيرة الذاتية طويلة جداً.'],
        'role' => ['الرجاء اختيار صلاحية صالحة.'],
        'skills' => ['يجب اختيار مهارة واحدة على الأقل.'],
        'agree' => ['يجب الموافقة على الشروط والأحكام.'],
    ]);
    $errors->put('default', $bag);

    // Share error bag with views globally
    view()->share('errors', $errors);
});

test('it renders form-input with different types and handles loading/error states', function () {
    // 1. Standard text input with label and helper text
    $view1 = $this->blade(
        '<x-form-input name="email" label="Email Address" type="email" placeholder="enter email" help-text="We will never share your email." />'
    );
    $view1->assertSee('Email Address');
    $view1->assertSee('enter email');
    $view1->assertSee('We will never share your email.');
    $view1->assertSee('type="email"', false);

    // 2. Loading state active
    $view2 = $this->blade(
        '<x-form-input name="first_name" label="First Name" :loading="true" />'
    );
    $view2->assertSee('جاري التحميل...');
    $view2->assertSee('animate-spin');

    // 3. Error state active
    $view3 = $this->blade(
        '<x-form-input name="username" label="Username" />'
    );
    $view3->assertSee('اسم المستخدم مطلوب.');
    $view3->assertSee('border-rose-500');

    // 4. File input style
    $view4 = $this->blade(
        '<x-form-input name="resume" label="Resume" type="file" />'
    );
    $view4->assertSee('اضغط لرفع ملف أو اسحبه هنا');
    $view4->assertSee('type="file"', false);
});

test('it renders form-textarea with rows and validation errors', function () {
    // 1. Textarea with custom rows
    $view1 = $this->blade(
        '<x-form-textarea name="notes" label="Notes" rows="6" placeholder="enter notes" />'
    );
    $view1->assertSee('Notes');
    $view1->assertSee('enter notes');
    $view1->assertSee('rows="6"', false);

    // 2. Validation error active
    $view2 = $this->blade(
        '<x-form-textarea name="bio" label="Biography" />'
    );
    $view2->assertSee('السيرة الذاتية طويلة جداً.');
    $view2->assertSee('border-rose-500');
});

test('it renders form-select options and support multiple selection', function () {
    $options = [
        'admin' => 'Administrator',
        'manager' => 'Manager',
        'employee' => 'Employee',
    ];

    // 1. Single select
    $view1 = $this->blade(
        '<x-form-select name="role" label="Access Role" :options="$options" selected="manager" placeholder="Select role" />',
        ['options' => $options]
    );

    $view1->assertSee('Access Role');
    $view1->assertSee('Administrator');
    $view1->assertSee('Manager');
    $view1->assertSee('Employee');
    $view1->assertSee('selected', false);

    // 2. Multi select
    $view2 = $this->blade(
        '<x-form-select name="roles" label="Access Roles" :options="$options" :multiple="true" />',
        ['options' => $options]
    );
    $view2->assertSee('multiple', false);
});

test('it renders form-multiselect dynamic badges and loading shimmers', function () {
    $options = [
        'laravel' => 'Laravel framework',
        'tailwind' => 'Tailwind CSS',
        'pest' => 'Pest PHP',
    ];

    // 1. Renders tags and placeholders
    $view1 = $this->blade(
        '<x-form-multiselect name="skills" label="Developer Skills" :options="$options" :selected="[\'laravel\', \'pest\']" />',
        ['options' => $options]
    );

    $view1->assertSee('Developer Skills');
    $view1->assertSee('Laravel framework');
    $view1->assertSee('Pest PHP');

    // Should have checked items
    $view1->assertSee('checked', false);

    // 2. Loading state shimmers
    $view2 = $this->blade(
        '<x-form-multiselect name="skills_loading" label="Skills" :options="$options" :loading="true" />',
        ['options' => $options]
    );
    $view2->assertSee('animate-pulse');
});

test('it renders form-toggle active and disabled states', function () {
    // 1. Active checked toggle with help text
    $view1 = $this->blade(
        '<x-form-toggle name="is_active" label="Status Active" :checked="true" help-text="Status shows on portal." />'
    );
    $view1->assertSee('Status Active');
    $view1->assertSee('Status shows on portal.');
    $view1->assertSee('checked', false);
    $view1->assertSee('sr-only', false);

    // 2. Disabled state toggle
    $view2 = $this->blade(
        '<x-form-toggle name="is_disabled" label="Status Locked" :checked="false" :disabled="true" />'
    );
    $view2->assertSee('disabled', false);
    $view2->assertSee('opacity-50', false);
});

test('it renders form-checkbox/radio values and labels', function () {
    // 1. Checkbox control
    $view1 = $this->blade(
        '<x-form-checkbox name="agree_terms" label="Agree terms" type="checkbox" help-text="Review rules before." />'
    );
    $view1->assertSee('Agree terms');
    $view1->assertSee('Review rules before.');
    $view1->assertSee('type="checkbox"', false);

    // 2. Radio control
    $view2 = $this->blade(
        '<x-form-checkbox name="gender" value="male" label="Male" type="radio" :checked="true" />'
    );
    $view2->assertSee('Male');
    $view2->assertSee('type="radio"', false);
    $view2->assertSee('checked', false);
});
