<?php

namespace Savagescape\Pdf;

use GuzzleHttp\Psr7;
use Illuminate\Support\Collection;
use Psr\Http\Message\StreamInterface;
use Savagescape\Pdf\Exceptions\InvalidFileException;

class Files
{
    private Collection $files;

    public function __construct()
    {
        $this->files = new Collection;
    }

    public function addString(string $filename, string $contents): self
    {
        if ($this->files->has($filename)) {
            throw InvalidFileException::duplicateFile($filename);
        }

        $this->addStream($filename, Psr7\Utils::streamFor($contents));

        return $this;
    }

    public function addFile(string $filename, string $path): self
    {
        if ($this->files->has($filename)) {
            throw InvalidFileException::duplicateFile($filename);
        }

        $this->addStream($filename, Psr7\Utils::tryFopen($path, 'r'));

        return $this;
    }

    public function addStream(string $filename, mixed $stream): self
    {
        if (! (is_resource($stream) && get_resource_type($stream) === 'stream') && ! $stream instanceof StreamInterface) {
            throw InvalidFileException::invalidStream($filename);
        } elseif ($this->files->has($filename)) {
            throw InvalidFileException::duplicateFile($filename);
        }

        $this->files->put($filename, $stream);

        return $this;
    }

    public function remove(string $filename): self
    {
        $this->files->forget($filename);

        return $this;
    }

    public function toMultipart(): array
    {
        return $this->files
            ->map(fn ($contents, $filename) => [
                'name' => 'files',
                'contents' => $contents,
                'filename' => $filename,
            ])
            ->values()
            ->toArray();
    }
}
