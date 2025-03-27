<?php

declare(strict_types=1);

namespace PhpExtractMetaUrl\Service;

use Exception;
use PhpExtractMetaUrl\Enum\ErrorType;

class CurlFetcher
{
    private const CURL_OPTS = [
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HEADER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36',
        CURLOPT_ENCODING => 1,
    ];

    /**
     * @throws Exception
     */
    public function fetch(string $url): string
    {
        $ch = curl_init();

        $opts = self::CURL_OPTS;
        $opts[CURLOPT_URL] = $url;
        
        curl_setopt_array($ch, $opts);

        $response = curl_exec($ch);

        if ($response === false) {
            throw new Exception(json_encode([
                'error_type' => ErrorType::CURL_REQUEST,
                'message' => sprintf('cURL error: "%s" - Code: %d', 
                    curl_error($ch), 
                    curl_errno($ch)
                ),
                'url' => $url
            ], JSON_THROW_ON_ERROR), 500);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (empty($response) || substr((string)$httpCode, 0, 2) !== '20') {
            throw new Exception(json_encode([
                'error_type' => ErrorType::CURL_REQUEST,
                'message' => "HTTP request failed with code {$httpCode}",
                'url' => $url
            ], JSON_THROW_ON_ERROR), 500);
        }

        return $response;
    }
}