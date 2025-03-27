<?php

declare(strict_types=1);

namespace PhpExtractMetaUrl;

use Exception;
use JsonException;
use PhpExtractMetaUrl\Enum\ErrorType;
use PhpExtractMetaUrl\Service\CurlFetcher;
use PhpExtractMetaUrl\Service\MetaExtractor;
use PhpExtractMetaUrl\Validator\UrlValidator;

class PhpExtractMetaUrl
{
    private string $url;
    private array $datas = [];
    private string $format = 'json';

    private UrlValidator $urlValidator;
    private CurlFetcher $curlFetcher;

    public function __construct(string $url)
    {
        $this->url = $url;
        $this->urlValidator = new UrlValidator();
        $this->curlFetcher = new CurlFetcher();

        $this->extract();
    }

    public function setFormat(string $format): void
    {
        $this->format = in_array($format, ['json', 'array']) ? $format : 'json';
    }

    /**
     * @return string|array
     * @throws JsonException
     */
    public function getResult()
    {
        return $this->format === 'json' 
            ? json_encode($this->datas, JSON_THROW_ON_ERROR) 
            : $this->datas;
    }

    /**
     * @throws Exception
     */
    private function extract(): void
    {
        if (!$this->urlValidator->isValid($this->url)) {
            throw new Exception(json_encode([
                'error_type' => ErrorType::INVALID_URL,
                'message' => "The URL provided is incorrect",
                'url' => $this->url
            ], JSON_THROW_ON_ERROR), 500);
        }

        $websiteData = $this->urlValidator->parseUrl($this->url);
        $this->datas['website'] = $websiteData;

        $htmlContent = $this->curlFetcher->fetch($this->url);
        $metaExtractor = new MetaExtractor($htmlContent, $websiteData);

        $this->datas = array_merge($this->datas, [
            'title' => $metaExtractor->extractTitle() ?? '',
            'description' => $metaExtractor->extractDescription() ?? '',
            'keywords' => $metaExtractor->extractKeywords() ?? [],
            'image' => $metaExtractor->extractImage() ?? null,
            'favicon' => $metaExtractor->extractFavicon($websiteData) ?? null,
            'rss' => $metaExtractor->extractRss() ?? [],
        ]);
    }
}