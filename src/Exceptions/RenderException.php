<?php

namespace Savagescape\Pdf\Exceptions;

use RuntimeException;

class RenderException extends RuntimeException
{
    private function __construct(string $message, int $code)
    {
        parent::__construct($message, $code);
    }

    public static function failedResponse(int $httpStatus): self
    {
        return new self('Failed to render PDF', $httpStatus);
    }
}
