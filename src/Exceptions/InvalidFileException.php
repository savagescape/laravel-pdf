<?php

namespace Savagescape\Pdf\Exceptions;

use RuntimeException;

class InvalidFileException extends RuntimeException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function invalidStream(string $filename): self
    {
        return new self("The contents of '$filename' was not a resource stream, or an instance of StreamInterface.");
    }

    public static function duplicateFile(string $filename): self
    {
        return new self("A file named '$filename' has already been attached.");
    }
}
