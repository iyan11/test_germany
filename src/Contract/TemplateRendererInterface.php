<?php

declare(strict_types=1);

namespace App\Contract;

interface TemplateRendererInterface
{
    /** @param array<string, string> $context */
    public function render(string $templatePath, array $context): string;
}
