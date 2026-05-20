@props([
    'type' => 'text',
    'name',
    'id' => null,
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'loading' => false,
    'helpText' => null,
    'class' => '',
])

@php
    $id = $id ?? $name;
    $hasError = $errors->has($name);
    $errorMsg = $errors->first($name);
    
    $inputClasses = "erp-input transition-all duration-200 text-xs sm:text-sm " . 
                    ($hasError ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 ' : 'focus:border-brand-500 focus:ring-brand-500/20 ') . 
                    ($disabled ? 'bg-slate-50 dark:bg-slate-900/60 text-slate-400 dark:text-slate-500 cursor-not-allowed border-slate-200 dark:border-slate-800 ' : ' ') . 
                    $class;
@endphp

<div class="space-y-1.5 w-full">
    @if($label)
        <div class="flex items-center justify-between">
            <label for="{{ $id }}" class="block text-xs font-bold text-slate-700 dark:text-slate-300 tracking-wide uppercase">
                {{ $label }}
                @if($required)
                    <span class="text-rose-500 ml-0.5 rtl:ml-0 rtl:mr-0.5" aria-hidden="true">*</span>
                @endif
            </label>
            
            @if($loading)
                <span class="inline-flex items-center gap-1 text-[10px] text-brand-500 font-bold animate-pulse">
                    <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'جاري التحميل...' : 'Loading...' }}</span>
                </span>
            @endif
        </div>
    @endif

    <div class="relative w-full">
        @if($type === 'file')
            <!-- Premium File Upload Area -->
            <div class="relative flex items-center justify-center border-2 border-dashed rounded-xl p-4 transition-colors {{ $hasError ? 'border-rose-300 bg-rose-50/10 dark:border-rose-900/30' : 'border-slate-200 dark:border-slate-800 hover:border-brand-400/50 bg-slate-50/50 dark:bg-slate-900/20' }}">
                <input 
                    type="file" 
                    name="{{ $name }}" 
                    id="{{ $id }}"
                    {{ $required ? 'required' : '' }}
                    {{ $disabled ? 'disabled' : '' }}
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                    onchange="document.getElementById('file-chosen-{{ $id }}').innerText = this.files[0]?.name || '{{ app()->getLocale() === 'ar' ? 'اضغط لرفع الملف' : 'Click to upload file' }}'"
                >
                <div class="text-center space-y-1.5 pointer-events-none">
                    <svg class="w-6 h-6 text-slate-400 dark:text-slate-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <div id="file-chosen-{{ $id }}" class="text-xs font-semibold text-slate-600 dark:text-slate-300">
                        {{ app()->getLocale() === 'ar' ? 'اضغط لرفع ملف أو اسحبه هنا' : 'Click to select file or drag here' }}
                    </div>
                </div>
            </div>
        @else
            <!-- Standard Inputs -->
            <input 
                type="{{ $type }}" 
                name="{{ $name }}" 
                id="{{ $id }}"
                value="{{ old($name, $value) }}"
                placeholder="{{ $placeholder }}"
                {{ $required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                aria-invalid="{{ $hasError ? 'true' : 'false' }}"
                @if($hasError) aria-describedby="{{ $id }}-error" @elseif($helpText) aria-describedby="{{ $id }}-help" @endif
                class="{{ $inputClasses }}"
            >
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
