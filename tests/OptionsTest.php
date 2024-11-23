<?php

use PHPUnit\Framework\TestCase;
use Savagescape\Pdf\Options;

class OptionsTest extends TestCase
{
    public function test_margins()
    {
        $options = new Options;
        $options->margins('10px', '20px', '30px', '40px');

        $this->assertEquals('10px', $options->marginTop);
        $this->assertEquals('20px', $options->marginRight);
        $this->assertEquals('30px', $options->marginBottom);
        $this->assertEquals('40px', $options->marginLeft);
    }

    public function test_to_multipart()
    {
        $options = new Options;
        $options->landscape();
        $options->marginTop('10px');
        $options->cookies([
            'name' => 'test',
            'value' => '123',
        ]);

        $multipart = $options->toMultipart();

        $expected = [
            ['name' => 'landscape', 'contents' => 'true'],
            ['name' => 'marginTop', 'contents' => '10px'],
            ['name' => 'cookies', 'contents' => json_encode(['name' => 'test', 'value' => '123'])],
        ];

        $this->assertEquals($expected, $multipart);
    }

    public function test_boolean_conversion()
    {
        $options = new Options;
        $options->printBackground();
        $options->omitBackground(false);

        $multipart = $options->toMultipart();

        $expected = [
            ['name' => 'printBackground', 'contents' => 'true'],
            ['name' => 'omitBackground', 'contents' => 'false'],
        ];

        $this->assertEquals($expected, $multipart);
    }
}
