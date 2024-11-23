<?php

namespace Savagescape\Pdf\Facades;

use Illuminate\Support\Facades\Facade;
use Savagescape\Pdf\Factory;

/**
 * @see \Savagescape\Pdf\Factory
 *
 * @method static \Savagescape\Pdf\Pdf fromHtml(string $html)
 * @method static \Savagescape\Pdf\Pdf fromView(string $path, array $data = [], array $mergeData = [])
 * @method static \Savagescape\Pdf\Pdf fromFile(string $path)
 */
class Pdf extends Facade
{
    public static function getFacadeAccessor()
    {
        return Factory::class;
    }
}
