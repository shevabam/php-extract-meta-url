<?php

declare(strict_types=1);

namespace PhpExtractMetaUrl\Enum;

final class ErrorType
{
    public const INVALID_URL = 'INVALID_URL';
    public const CURL_REQUEST = 'CURL_REQUEST_FAILED';
    public const PARSING = 'PARSING_FAILED';
}