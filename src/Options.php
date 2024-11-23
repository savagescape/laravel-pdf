<?php

namespace Savagescape\Pdf;

use Illuminate\Support\Fluent;

/**
 * Supports all form fields Gotenberg uses.
 *
 * @see https://gotenberg.dev/docs/routes
 *
 * @example $options->landscape()->marginTop(10)->marginBottom(20)->printBackground()
 * @example new Options(['landscape' => true, 'marginTop' => 10, 'marginBottom' => 20, 'printBackground' => true])
 *
 * @see https://gotenberg.dev/docs/routes#page-properties-chromium
 *
 * @method $this singlePage(bool $singlePage = true)
 * @method $this paperWidth(string $paperWidth)
 * @method $this paperHeight(string $paperHeight)
 * @method $this marginTop(string $marginTop)
 * @method $this marginBottom(string $marginBottom)
 * @method $this marginLeft(string $marginLeft)
 * @method $this marginRight(string $marginRight)
 * @method $this preferCssPageSize(bool $preferCssPageSize = true)
 * @method $this generateDocumentOutline(bool $generateDocumentOutline = true)
 * @method $this printBackground(bool $printBackground = true)
 * @method $this omitBackground(bool $omitBackground = true)
 * @method $this landscape(bool $landscape = true)
 * @method $this scale(float|string $scale)
 * @method $this nativePageRanges(string $nativePageRanges)
 *
 * @see https://gotenberg.dev/docs/routes#wait-before-rendering-chromium
 *
 * @method $this waitDelay(string $waitDelay)
 * @method $this waitForExpression(string $waitForExpression)
 *
 * @see https://gotenberg.dev/docs/routes#emulated-media-type-chromium
 *
 * @method $this emulatedMediaType(string $emulatedMediaType)
 *
 * @see https://gotenberg.dev/docs/routes#cookies-chromium
 *
 * @method $this cookies(string $cookies)
 *
 * @see https://gotenberg.dev/docs/routes#custom-http-headers-chromium
 *
 * @method $this userAgent(string $userAgent)
 * @method $this extraHttpHeaders(string $extraHttpHeaders)
 *
 * @see https://gotenberg.dev/docs/routes#invalid-http-status-codes-chromium
 *
 * @method $this failOnHttpStatusCodes(string $failOnHttpStatusCodes)
 * @method $this failOnResourceHttpStatusCodes(string $failOnResourceHttpStatusCodes)
 *
 * @see https://gotenberg.dev/docs/routes#network-errors-chromium
 *
 * @method $this failOnResourceLoadingFailed(bool $failOnResourceLoadingFailed = true)
 *
 * @see https://gotenberg.dev/docs/routes#console-exceptions-chromium
 *
 * @method $this failOnConsoleExceptions(bool $failOnConsoleExceptions = true)
 *
 * @see https://gotenberg.dev/docs/routes#performance-mode-chromium
 *
 * @method $this skipNetworkIdleEvent(bool $skipNetworkIdleEvent = true)
 *
 * @see https://gotenberg.dev/docs/routes#pdfa-chromium
 *
 * @method $this pdfa(bool $pdfa = true)
 * @method $this pdfua(bool $pdfua = true)
 *
 * @see https://gotenberg.dev/docs/routes#metadata-chromium
 *
 * @method $this metadata(array $metadata)
 */
class Options extends Fluent
{
    private const JSON_ATTRIBUTES = [
        'cookies',
        'extraHttpHeaders',
        'metadata',
    ];

    /**
     * Shorthand for margin top/right/bottom/left
     */
    public function margins(string $top, ?string $right = null, ?string $bottom = null, ?string $left = null): self
    {
        $right = $right ?? $top;
        $bottom = $bottom ?? $top;
        $left = $left ?? $right;

        return $this
            ->marginTop($top)
            ->marginRight($right)
            ->marginBottom($bottom)
            ->marginLeft($left);
    }

    public function toMultipart(): array
    {
        $attributes = $this->toArray();
        $multipart = [];

        foreach ($attributes as $key => $value) {
            if (is_null($value)) {
                continue;
            } elseif (in_array($key, self::JSON_ATTRIBUTES, true)) {
                $value = json_encode($value);
            } elseif (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            $multipart[] = [
                'name' => $key,
                'contents' => (string) $value,
            ];
        }

        return $multipart;
    }
}
