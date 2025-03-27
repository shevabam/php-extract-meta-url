<?php

declare(strict_types=1);

namespace PhpExtractMetaUrl\Service;

use DOMDocument;
use DOMXPath;
use PhpExtractMetaUrl\Service\UrlNormalizer;

class MetaExtractor
{
    private const RSS_TYPES = [
        'application/rss+xml', 'application/atom+xml', 'application/rdf+xml',
        'application/rss', 'application/atom', 'application/rdf',
        'text/rss+xml', 'text/atom+xml', 'text/rdf+xml',
        'text/rss', 'text/atom', 'text/rdf',
    ];

    private DOMDocument $dom;
    private UrlNormalizer $urlNormalizer;

    public function __construct(string $htmlContent, array $websiteData)
    {
        $this->dom = $this->createDomDocument($htmlContent);
        $this->urlNormalizer = new UrlNormalizer($websiteData);
    }

    private function createDomDocument(string $htmlContent): DOMDocument
    {
        $dom = new DOMDocument();
        
        libxml_use_internal_errors(true);
        
        $dom->loadHTML(
            mb_encode_numericentity(
                htmlspecialchars_decode(
                    htmlentities($htmlContent, ENT_NOQUOTES, 'UTF-8', false),
                    ENT_NOQUOTES
                ), 
                [0x80, 0x10FFFF, 0, ~0],
                'UTF-8'
            ), 
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        
        libxml_clear_errors();

        return $dom;
    }

    public function extractTitle(): ?string
    {
        $titleNodes = $this->dom->getElementsByTagName("title");

        return $titleNodes->length > 0 
            ? $titleNodes->item(0)->nodeValue 
            : null;
    }

    public function extractDescription(): ?string
    {
        $xpath = new DOMXPath($this->dom);
        $nodes = $xpath->query('//head/meta[@name="description"]');

        return $nodes->count() > 0 && !empty($nodes->item(0)->getAttribute('content'))
            ? $nodes->item(0)->getAttribute('content')
            : null;
    }

    public function extractKeywords(): ?array
    {
        $xpath = new DOMXPath($this->dom);
        $nodes = $xpath->query('//head/meta[@name="keywords"]');

        if ($nodes->count() > 0 && !empty($nodes->item(0)->getAttribute('content'))) {
            return array_map('trim', explode(',', $nodes->item(0)->getAttribute('content')));
        }

        return null;
    }

    public function extractImage(): ?string
    {
        $xpath = new DOMXPath($this->dom);

        $imageSources = [
            '//head/meta[@property="og:image"]',
            '//head/meta[@name="twitter:image"]',
            '//head/meta[@itemprop="image"]',
            '//link[@rel="image_src"]',
            '//body//img[contains(@class, "featured") or contains(@class, "main-image")]'
        ];

        foreach ($imageSources as $imageSource) {
            $nodes = $xpath->query($imageSource);
            
            if ($nodes->count() > 0) {
                $imageUrl = $nodes->item(0)->getAttribute('content') 
                    ?? $nodes->item(0)->getAttribute('src');
                
                if (!empty($imageUrl)) {
                    return $this->urlNormalizer->normalize($imageUrl);
                }
            }
        }

        return null;
    }

    public function extractFavicon(array $websiteData): ?string
    {
        $xpath = new DOMXPath($this->dom);

        $faviconSources = [
            '//head/link[@rel="icon"]',
            '//head/link[@rel="shortcut icon"]',
            '//head/link[@rel="apple-touch-icon"]',
            '//*[contains(@href, "favicon")]'
        ];

        foreach ($faviconSources as $faviconSource) {
            $nodes = $xpath->query($faviconSource);
            
            if ($nodes->count() > 0) {
                $faviconUrl = $nodes->item(0)->getAttribute('href');
                
                if (!empty($faviconUrl)) {
                    return $this->urlNormalizer->normalize($faviconUrl);
                }
            }
        }

        return sprintf('%s://%s/favicon.ico', 
            $websiteData['scheme'], 
            $websiteData['host']
        );
    }

    public function extractRss(): array
    {
        $xpath = new DOMXPath($this->dom);
        $links = $xpath->query('//head/link[@rel="alternate"]');

        $rss = [];
        foreach ($links as $link) {
            $linkType = $link->getAttribute('type');

            if (in_array($linkType, self::RSS_TYPES)) {
                $rss[] = $link->getAttribute('href');
            }
        }

        return $rss;
    }
}