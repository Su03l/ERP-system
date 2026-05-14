<?php

namespace App\DTOs;

class KpiDefinition
{
    public function __construct(
        public readonly string $key,
        public readonly string $module,
        public readonly string $labelAr,
        public readonly ?string $labelEn,
        public readonly ?string $descriptionAr,
        public readonly ?string $descriptionEn,
        public readonly ?string $requiredPermission,
        public readonly string $resolverClass,
        public readonly bool $supportsDateRange = true,
        public readonly ?string $defaultDateRange = null,
    ) {}

    public function label(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'en' ? $this->labelEn ?? $this->labelAr : $this->labelAr;
    }

    /**
     * @return array{key: string, module: string, label_ar: string, label_en: string|null, description_ar: string|null, description_en: string|null, required_permission: string|null, resolver_class: string, supports_date_range: bool, default_date_range: string|null}
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'module' => $this->module,
            'label_ar' => $this->labelAr,
            'label_en' => $this->labelEn,
            'description_ar' => $this->descriptionAr,
            'description_en' => $this->descriptionEn,
            'required_permission' => $this->requiredPermission,
            'resolver_class' => $this->resolverClass,
            'supports_date_range' => $this->supportsDateRange,
            'default_date_range' => $this->defaultDateRange,
        ];
    }
}
