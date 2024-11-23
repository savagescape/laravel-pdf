
<?php

use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Savagescape\Pdf\Exceptions\InvalidFileException;
use Savagescape\Pdf\Files;

class FilesTest extends TestCase
{
    public function test_add_string()
    {
        $files = new Files;
        $files->addString('test.txt', 'This is a test.');

        $multipart = $files->toMultipart();

        $this->assertCount(1, $multipart);
        $this->assertEquals('test.txt', $multipart[0]['filename']);
        $this->assertInstanceOf(StreamInterface::class, $multipart[0]['contents']);
        $this->assertEquals('This is a test.', (string) $multipart[0]['contents']->getContents());
    }

    public function test_add_file()
    {
        $files = new Files;
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'This is a test file.');

        $files->addFile('testfile.txt', $tempFile);

        $multipart = $files->toMultipart();

        $this->assertCount(1, $multipart);
        $this->assertEquals('testfile.txt', $multipart[0]['filename']);
        $this->assertIsResource($multipart[0]['contents']);

        unlink($tempFile);
    }

    public function test_add_stream()
    {
        $files = new Files;
        $stream = Utils::streamFor('This is a stream.');

        $files->addStream('stream.txt', $stream);

        $multipart = $files->toMultipart();

        $this->assertCount(1, $multipart);
        $this->assertEquals('stream.txt', $multipart[0]['filename']);
        $this->assertEquals('This is a stream.', (string) $multipart[0]['contents']);
    }

    public function test_duplicate_file_exception()
    {
        $this->expectException(InvalidFileException::class);

        $files = new Files;
        $files->addString('duplicate.txt', 'First instance.');
        $files->addString('duplicate.txt', 'Second instance.');
    }

    public function test_invalid_stream_exception()
    {
        $this->expectException(InvalidFileException::class);

        $files = new Files;
        $files->addStream('invalid.txt', 'This is not a stream.');
    }

    public function test_remove_file()
    {
        $files = new Files;
        $files->addString('remove.txt', 'This will be removed.');
        $files->remove('remove.txt');

        $multipart = $files->toMultipart();

        $this->assertCount(0, $multipart);
    }
}
