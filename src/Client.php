<?php

namespace Savagescape\Pdf;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class Client
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = rtrim($url, '/');
    }

    public function createChromeRequest(): PendingRequest
    {
        return Http::baseUrl("{$this->url}/forms/chromium");
    }
}
