@props([
    'name',
    'id' => null,
    'label' => null,
    'options' => [], // Key/Value pairs
    'selected' => [], // Array of pre-selected keys
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
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $placeholder = $placeholder ?? ($isAr ? 'اختر خيارات متعددة...' : 'Select options...');
@endphp

<div class="space-y-1.5 w-full">
    @if($label)
        <div class="flex items-center justify-between">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 tracking-wide uppercase">
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
                    <span>{{ $isAr ? 'جاري التحميل...' : 'Loading...' }}</span>
                </span>
            @endif
        </div>
    @endif

    <!-- Container wrapper -->
    <div class="relative w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-premium {{ $hasError ? 'border-rose-500 ring-1 ring-rose-500/20' : '' }} {{ $class }}">
        
        <!-- Live Selected Badges Pane -->
        <div class="p-2 border-b border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/30 flex flex-wrap gap-1.5 min-h-[42px]" id="badge-container-{{ $id }}">
            @php
                $selectedArray = old($name, $selected) ?? [];
                if (!is_array($selectedArray)) {
                    $selectedArray = [$selectedArray];
                }
            @endphp
            
            @if(empty($selectedArray))
                <span class="text-xs text-slate-400 p-1 select-none" id="placeholder-{{ $id }}">{{ $placeholder }}</span>
            @endif

            @foreach($options as $val => $text)
                @if(in_array((string)$val, array_map('strval', $selectedArray)))
                    <span 
                        id="badge-{{ $id }}-{{ $val }}"
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-brand-500/10 dark:bg-brand-500/20 text-brand-700 dark:text-brand-400 text-[11px] font-bold"
                    >
                        <span>{{ $text }}</span>
                        @if(!$disabled)
                            <button 
                                type="button" 
                                onclick="deselectMultiselectOption('{{ $id }}', '{{ $val }}')"
                                class="text-brand-500 hover:text-brand-700 dark:hover:text-brand-300 font-bold focus:outline-none"
                            >
                                &times;
                            </button>
                        @endif
                    </span>
                @endif
            @endforeach
        </div>

        <!-- Options Checkbox List -->
        <div class="max-h-[160px] overflow-y-auto p-2.5 divide-y divide-slate-100 dark:divide-slate-800/60" id="options-list-{{ $id }}">
            @if($loading)
                @for($i = 0; $i < 3; $i++)
                    <div class="py-2 flex items-center gap-3 animate-pulse">
                        <div class="w-4 h-4 bg-slate-200 dark:bg-slate-800 rounded"></div>
                        <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-1/3"></div>
                    </div>
                @endfor
            @elseif(empty($options))
                <div class="py-4 text-center text-xs text-slate-400">
                    {{ $isAr ? 'لا توجد خيارات متاحة' : 'No options available' }}
                </div>
            @else
                @foreach($options as $val => $text)
                    @php
                        $isChecked = in_array((string)$val, array_map('strval', $selectedArray));
                    @endphp
                    <label 
                        class="flex items-center gap-2.5 py-2 px-1.5 hover:bg-slate-50 dark:hover:bg-slate-800/40 rounded-lg cursor-pointer transition-colors text-xs font-semibold text-slate-700 dark:text-slate-300"
                        id="option-label-{{ $id }}-{{ $val }}"
                    >
                        <input 
                            type="checkbox" 
                            name="{{ $name }}[]" 
                            value="{{ $val }}"
                            id="checkbox-{{ $id }}-{{ $val }}"
                            {{ $isChecked ? 'checked' : '' }}
                            {{ $disabled ? 'disabled' : '' }}
                            onclick="handleMultiselectToggle('{{ $id }}', '{{ $val }}', '{{ addslashes($text) }}', this)"
                            class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-slate-300 dark:border-slate-700 dark:bg-slate-950 cursor-pointer multiselect-item-checkbox"
                        >
                        <span>{{ $text }}</span>
                    </label>
                @endforeach
            @endif
        </div>
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

<!-- Interactive JS Controller -->
<script>
    function handleMultiselectToggle(id, val, text, checkbox) {
        const badgeContainer = document.getElementById(`badge-container-${id}`);
        const placeholder = document.getElementById(`placeholder-${id}`);
        
        if (checkbox.checked) {
            // Remove placeholder if present
            if (placeholder) placeholder.style.display = 'none';

            // Check if badge already exists
            if (document.getElementById(`badge-${id}-${val}`)) return;

            // Append new badge
            const badge = document.createElement('span');
            badge.id = `badge-${id}-${val}`;
            badge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-brand-500/10 dark:bg-brand-500/20 text-brand-700 dark:text-brand-400 text-[11px] font-bold animate-pulse-once';
            badge.innerHTML = `
                <span>${text}</span>
                <button type="button" onclick="deselectMultiselectOption('${id}', '${val}')" class="text-brand-500 hover:text-brand-700 dark:hover:text-brand-300 font-bold focus:outline-none">&times;</button>
            `;
            badgeContainer.appendChild(badge);
        } else {
            // Remove badge
            const badge = document.getElementById(`badge-${id}-${val}`);
            if (badge) badge.remove();

            // Re-show placeholder if no badges left
            if (badgeContainer.querySelectorAll('[id^="badge-"]').length === 0 && placeholder) {
                placeholder.style.display = 'inline';
            }
        }
    }

    function deselectMultiselectOption(id, val) {
        const checkbox = document.getElementById(`checkbox-${id}-${val}`);
        const badge = document.getElementById(`badge-${id}-${val}`);
        const badgeContainer = document.getElementById(`badge-container-${id}`);
        const placeholder = document.getElementById(`placeholder-${id}`);

        if (checkbox) checkbox.checked = false;
        if (badge) badge.remove();

        // Re-show placeholder if no badges left
        if (badgeContainer.querySelectorAll('[id^="badge-"]').length === 0 && placeholder) {
            placeholder.style.display = 'inline';
        }
    }
</script>
