@props([
    'type' => 'checkbox', // checkbox or radio
    'name',
    'id' => null,
    'label' => null,
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'helpText' => null,
    'class' => '',
])

@php
    $id = $id ?? ($name . '_' . $value);
    $hasError = $errors->has($name);
    $errorMsg = $errors->first($name);
    $isChecked = old($name) !== null ? old($name) == $value : $checked;
    
    $controlClasses = ($type === 'radio' ? 'rounded-full ' : 'rounded ') . 
                      "w-4 h-4 text-brand-600 focus:ring-brand-500 border-slate-300 dark:border-slate-700 dark:bg-slate-950 cursor-pointer " . 
                      ($disabled ? 'opacity-50 cursor-not-allowed ' : ' ') . 
                      $class;
@endphp

<div class="space-y-1 w-full">
    <div class="flex items-start gap-2.5">
        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $id }}"
            value="{{ $value }}"
            {{ $isChecked ? 'checked' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
            @if($hasError) aria-describedby="{{ $id }}-error" @elseif($helpText) aria-describedby="{{ $id }}-help" @endif
            class="{{ $controlClasses }}"
        >
        @if($label)
            <div class="space-y-0.5">
                <label for="{{ $id }}" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer select-none {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}">
                    {{ $label }}
                </label>
                @if($helpText && !$hasError)
                    <p id="{{ $id }}-help" class="text-[10px] text-slate-400 leading-normal select-none">
                        {{ $helpText }}
                    </p>
                @endif
            </div>
        @endif
    </div>

    @if($hasError)
        <p id="{{ $id }}-error" class="text-[11px] font-bold text-rose-600 dark:text-rose-400 mt-1 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <span>{{ $errorMsg }}</span>
        </p>
    @endif
</div>
