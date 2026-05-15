<?php

namespace App\DTOs;

class ReportDefinition
{
    /**
     * @param  array<int, string>  $supportedFilters
     * @param  array<int, string>  $supportedExports
     */
    public function __construct(
        public readonly string $key,
        public readonly string $module,
        public readonly string $nameAr,
        public readonly ?string $nameEn,
        public readonly ?string $descriptionAr,
        public readonly ?string $descriptionEn,
        public readonly ?string $requiredPermission,
        public readonly string $resolverClass,
        public readonly array $supportedFilters = [],
        public readonly array $supportedExports = ['csv'],
    ) {}

    public function name(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'en' ? $this->nameEn ?? $this->nameAr : $this->nameAr;
    }

    public function supportsExport(string $format): bool
    {
        return in_array($format, $this->supportedExports, true);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'module' => $this->module,
            'name_ar' => $this->nameAr,
            'name_en' => $this->nameEn,
            'description_ar' => $this->descriptionAr,
            'description_en' => $this->descriptionEn,
            'required_permission' => $this->requiredPermission,
            'resolver_class' => $this->resolverClass,
            'supported_filters' => $this->supportedFilters,
            'supported_exports' => $this->supportedExports,
        ];
    }
}
