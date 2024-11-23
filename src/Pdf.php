<?php

namespace Savagescape\Pdf;

use Closure;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Client\PendingRequest;
use Psr\Http\Message\StreamInterface;
use Savagescape\Pdf\Exceptions\RenderException;
use Symfony\Component\HttpFoundation\Response;

class Pdf implements Responsable
{
    public function __construct(
        private Filesystem $filesystem,
        private ResponseFactory $response,
        private PendingRequest $request,
        private Options $options,
        private Files $files,
    ) {}

    private function render(): StreamInterface
    {
        $multipartData = array_merge(
            $this->files->toMultipart(),
            $this->options->toMultipart(),
        );

        $response = $this->request
            ->asMultipart()
            ->post('convert/html', $multipartData);

        if ($response->failed()) {
            throw RenderException::failedResponse($response->status());
        }

        return $response->toPsrResponse()->getBody();
    }

    /**
     * Configure PDF generation in Gotenberg
     *
     * @param  Closure(Options $options):void  $callback
     */
    public function options(Closure $callback): self
    {
        $callback($this->options);

        return $this;
    }

    /**
     * @param  Closure(Files $files):void  $callback
     */
    public function files(Closure $callback): self
    {
        $callback($this->files);

        return $this;
    }

    /**
     * Short hand for adding a file inside the PDF
     * Equivalent to:
     * $pdf->files(fn (Files $files) => $files->addFile($filename, $path))
     */
    public function attach(string $filename, string $path): self
    {
        $this->files->addFile($filename, $path);

        return $this;
    }

    public function stream(): StreamInterface
    {
        return $this->render();
    }

    /**
     * Store the PDF to the filesystem
     */
    public function store(string $filename): bool
    {
        return $this->filesystem->put($filename, $this->render());
    }

    /**
     * Triggers a HTTP download in the browser
     */
    public function download(string $filename = 'download.pdf', string $disposition = 'attachment'): Response
    {
        $stream = $this->render();

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Size' => $stream->getSize(),
        ];

        return $this->response->streamDownload(
            fn () => fpassthru($stream->detach()),
            $filename,
            $headers,
            $disposition
        );
    }

    /**
     * Display the PDF inline in the browser
     */
    public function inline(string $filename): Response
    {
        return $this->download($filename, 'inline');
    }

    public function toResponse($request): Response
    {
        return $this->inline('download.pdf');
    }
}
