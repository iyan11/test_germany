<?php

declare(strict_types=1);

namespace App\Infrastructure\Template;

use App\Contract\TemplateRendererInterface;
use RuntimeException;

final class HtmlTemplateRenderer implements TemplateRendererInterface
{
    public function render(string $templatePath, array $context): string
    {
        if (!is_file($templatePath)) {
            throw new RuntimeException('Template not found: ' . $templatePath);
        }

        $template = (string) file_get_contents($templatePath);
        foreach ($context as $key => $value) {
            $safeValue = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            if ($key === 'DESCRIPTION_TEXT' || str_starts_with($key, 'BULLET_')) {
                $safeValue = nl2br($safeValue);
            }
            $template = str_replace('{{' . $key . '}}', $safeValue, $template);
        }

        return $template;
    }
}
