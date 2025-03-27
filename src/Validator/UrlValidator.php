<?php

declare(strict_types=1);

namespace PhpExtractMetaUrl\Validator;

class UrlValidator
{
    public function isValid(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public function parseUrl(string $url): array
    {
        $parsedUrl = parse_url($url);
        return [
            'url' => $url,
            'scheme' => $parsedUrl['scheme'] ?? '',
            'host' => $parsedUrl['host'] ?? '',
            'path' => $parsedUrl['path'] ?? '',
        ];
    }
}