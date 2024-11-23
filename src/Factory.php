<?php

namespace Savagescape\Pdf;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory as View;
use Illuminate\Filesystem\Filesystem;

class Factory
{
    public function __construct(
        private Client $client,
        private Filesystem $filesystem,
        private ResponseFactory $response,
        private View $view
    ) {}

    private function get(Files $files): Pdf
    {
        return new Pdf(
            $this->filesystem,
            $this->response,
            $this->client->createChromeRequest(),
            new Options,
            $files
        );
    }

    public function fromHtml(string $html): Pdf
    {
        $files = new Files;
        $files->addString('index.html', $html);

        return $this->get($files);
    }

    public function fromView(string $path, array $data = [], array $mergeData = []): Pdf
    {
        $html = $this->view->make($path, $data, $mergeData)->render();

        return $this->fromHtml($html);
    }

    public function fromFile(string $path): Pdf
    {
        $files = new Files;
        $files->addFile('index.html', $path);

        return $this->get($files);
    }
}
