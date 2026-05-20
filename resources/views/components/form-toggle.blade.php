@props([
    'name',
    'id' => null,
    'label' => null,
    'checked' => false,
    'value' => '1',
    'disabled' => false,
    'helpText' => null,
    'class' => '',
])

@php
    $id = $id ?? $name;
    $hasError = $errors->has($name);
    $errorMsg = $errors->first($name);
    $isChecked = old($name) !== null ? old($name) == $value : $checked;
@endphp

<div class="space-y-1.5 w-full">
    <div class="flex items-start gap-3">
        <!-- Switch Toggle Control -->
        <label class="relative inline-flex items-center cursor-pointer select-none shrink-0" id="toggle-container-{{ $id }}">
            <input 
                type="checkbox" 
                name="{{ $name }}" 
                id="{{ $id }}"
                value="{{ $value }}"
                {{ $isChecked ? 'checked' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                aria-invalid="{{ $hasError ? 'true' : 'false' }}"
                @if($hasError) aria-describedby="{{ $id }}-error" @elseif($helpText) aria-describedby="{{ $id }}-help" @endif
                class="sr-only peer"
            >
            <div class="w-9 h-5 bg-slate-200 dark:bg-slate-800 rounded-full peer peer-focus:ring-2 peer-focus:ring-brand-500/20 dark:peer-focus:ring-brand-500/10 peer-checked:after:translate-x-full peer-checked:after:rtl:-translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:rtl:left-auto after:rtl:right-[2px] after:bg-white after:border-slate-300 dark:after:border-slate-700 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-500 {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"></div>
        </label>

        <!-- Label / Caption details -->
        @if($label)
            <div class="space-y-0.5">
                <label for="{{ $id }}" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide cursor-pointer {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}">
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
