<?php

declare(strict_types=1);

namespace PhpExtractMetaUrl\Service;

class UrlNormalizer
{
    private array $websiteData;

    public function __construct(array $websiteData)
    {
        $this->websiteData = $websiteData;
    }

    public function normalize(string $url): string
    {
        // URL absolue
        if (parse_url($url, PHP_URL_SCHEME) !== null) {
            return $url;
        }

        $baseUrl = sprintf('%s://%s', 
            $this->websiteData['scheme'], 
            $this->websiteData['host']
        );
        
        // URL commençant par //
        if (substr($url, 0, 2) === '//') {
            return sprintf('%s:%s', $this->websiteData['scheme'], $url);
        }

        // URL relative au domaine
        if (substr($url, 0, 1) === '/') {
            return $baseUrl . $url;
        }

        // URL relative au chemin courant
        return $baseUrl . '/' . $url;
    }
}