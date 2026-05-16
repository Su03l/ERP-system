<?php

namespace App\DTOs;

class WebhookEventDefinition
{
    public function __construct(
        public readonly string $name,
        public readonly string $module,
        public readonly string $payloadResolver,
        public readonly ?string $requiredModule = null,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'module' => $this->module,
            'payload_resolver' => $this->payloadResolver,
            'required_module' => $this->requiredModule,
        ];
    }
}
