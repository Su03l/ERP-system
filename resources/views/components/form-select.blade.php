@props([
    'name',
    'id' => null,
    'label' => null,
    'options' => [], // Can be array like: ['key' => 'Value'] or index list
    'selected' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'multiple' => false,
    'helpText' => null,
    'class' => '',
])

@php
    $id = $id ?? $name;
    $hasError = $errors->has($name);
    $errorMsg = $errors->first($name);
    
    $selectClasses = "erp-input transition-all duration-200 text-xs sm:text-sm bg-[image:var(--tw-select-arrow)] appearance-none cursor-pointer " . 
                     ($hasError ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 ' : 'focus:border-brand-500 focus:ring-brand-500/20 ') . 
                     ($disabled ? 'bg-slate-50 dark:bg-slate-900/60 text-slate-400 dark:text-slate-500 cursor-not-allowed border-slate-200 dark:border-slate-800 ' : ' ') . 
                     $class;
@endphp

<div class="space-y-1.5 w-full">
    @if($label)
        <label for="{{ $id }}" class="block text-xs font-bold text-slate-700 dark:text-slate-300 tracking-wide uppercase">
            {{ $label }}
            @if($required)
                <span class="text-rose-500 ml-0.5 rtl:ml-0 rtl:mr-0.5" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div class="relative w-full">
        <select 
            name="{{ $name }}{{ $multiple ? '[]' : '' }}" 
            id="{{ $id }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $multiple ? 'multiple' : '' }}
            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
            @if($hasError) aria-describedby="{{ $id }}-error" @elseif($helpText) aria-describedby="{{ $id }}-help" @endif
            class="{{ $selectClasses }}"
        >
            @if($placeholder && !$multiple)
                <option value="" disabled {{ is_null(old($name, $selected)) ? 'selected' : '' }}>{{ $placeholder }}</option>
            @endif

            @foreach($options as $val => $text)
                @php
                    $isSelect = false;
                    $oldVal = old($name, $selected);
                    
                    if ($multiple && is_array($oldVal)) {
                        $isSelect = in_array($val, $oldVal);
                    } else {
                        $isSelect = (string) $val === (string) $oldVal;
                    }
                @endphp
                <option value="{{ $val }}" {{ $isSelect ? 'selected' : '' }}>
                    {{ $text }}
                </option>
            @endforeach
        </select>
        
        @if(!$multiple)
            <!-- Absolute Right Down Arrow Indicator -->
            <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 rtl:right-auto rtl:left-0 rtl:pl-3.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </span>
        @endif
    </div>

    @if($hasError)
        <p id="{{ $id }}-error" class="text-[11px] font-bold text-rose-600 dark:text-rose-400 mt-1 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <span>{{ $errorMsg }}</span>
        </p>
    @elseif($helpText)
        <p id="{{ $id }}-help" class="text-[10px] text-slate-400 mt-1 leading-normal">
            {{ $helpText }}
        </p>
    @endif
</div>
