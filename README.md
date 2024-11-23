# Savagescape\Pdf

Render PDFs from HTML using a headless instance of Chromium via [gotenberg.dev](https://gotenberg.dev/)

See also:
- GitHub Repo: https://github.com/gotenberg/gotenberg
- Docker Container: https://hub.docker.com/r/gotenberg/gotenberg

---

## Configuration

### Example Docker Compose service
```yml
  gotenberg:
    image: gotenberg/gotenberg:8
    restart: unless-stopped
```

### Configuring Laravel
1. Add a new service to `config/services.php` called `gotenberg` with a `url`:
    ```php
    'gotenberg' => [
        'url' => env('GOTENBERG_URL', 'http://gotenberg:3000'),
    ],
    ```
2. Optional, set `GOTENBERG_URL` in .env to point to the correct container name.
3. It should be automatically discovered, but if not, add the service provider to Laravel's app configuration `bootstrap/app.php`:
    ```php
    ->withProviders([
        Savagescape\Pdf\ServiceProvider::class,
    ])
    ```

---

## Usage

You can either create a new `Savagescape\Pdf\Pdf` instance via the Factory or through the Facade.

### Factory
```php
use Savagescape\Pdf\Factory as PdfFactory;

class MyController
{
    // Let the router autowire the factory for you
    public function myRoute(PdfFactory $pdfFactory)
    {
        // or pull it directly from the container
        // $pdfFactory = app(PdfFactory::class);

        // From a string of HTML
        $pdf = $pdfFactory->fromHtml('<h1>Hello world</h1>');

        // or from a raw file
        $pdf = $pdfFactory->fromFile(resource_path('hello-world.html'));

        // or from a Blade view
        $pdf = $pdfFactory->fromView('hello-world', ['name' => 'Bob']);
    }
}
```

### Facade

```php
use Savagescape\Pdf\Facades\Pdf;

// From a string of HTML
$pdf = Pdf::fromHtml('<h1>Hello world</h1>');

// or from a raw file
$pdf Pdf::fromFile(resource_path('hello-world.html'));

// or from a Blade view
$pdf = Pdf::fromView('hello-world', ['name' => 'Bob']);
```

## Advanaced usage

### Setting render options

To configure how Gotenberg renders the HTML into a PDF, you can configure any of the form field options inside of the `options()` callback.

Some shorthand syntax is included, for example `margins($top, $right, $bottom, $left)

See: https://gotenberg.dev/docs/routes

```php
use Savagescape\Pdf\Facades\Pdf;
use Savagescape\Pdf\Options;

$pdf = Pdf::fromHtml('<h1>Hello world</h1>')
    ->options(function (Options $options) {
        // $options is a Fluent object mapping to Gotenberg options

        // sets "landscape" to "true"
        $options->landscape();

        // sets "printBackground" to "false"
        $options->printBackground(false);

        // sets "marginTop" to "1"
        $options->marginTop(1);

        // and you can chain them
        // sets "marginLeft" to "2" and "marginRight" to "3"
        $options->marginLeft(2)->marginRight(3);

        // Clear "marginBottom"
        $options->marginBottom(null);
    });
```

### Including other files

The HTML/file/view you start from is automatically mapped to `index.html`. To attach additional resources (like CSS, images, etc) you must pass them along to Gotenberg as well.

You can only add one file per-filename, trying to replace an already attached file will throw an exception.

You should reference any attached files using relative paths, eg: use `<img src="image.png">` instead of `<img src="/image.png">`

Three filenames are reserved for special use by Gotenberg:
* `index.html` For the actual contents of your PDF (this is already handled for you.)
* `header.html` To render a header at the top of every page.
* `footer.html` To render a footer at the bottom of every page.

See: https://gotenberg.dev/docs/routes#header-footer-chromium

> [!NOTE]
> Attached files need to be transfered to the Gotenberg container along side your HTML. The larger the files you attach, the longer this will take.

```php
use Savagescape\Pdf\Facades\Pdf;
use Savagescape\Pdf\Files;

// To simply attach an existing file, you can use ->attach()
// Also note that the filename you pass along doesn't necessarily
// have to match whats on disk
$pdf = Pdf::fromHtml('<link rel="stylesheet" href="style.css"><h1>Hello world</h1>')
    ->attach('style.css', resource_path('path/to/my/custom.css'));

// To attach a file from a stream or a string you can interact with the Files object directly
$pdf = Pdf::fromHtml('<link rel="stylesheet" href="style.css"><img src="logo.png"><h1>Hello world</h1>')
    ->files(function (Files $files) {
        // Simply pass a string
        $files->addString('style.css', 'h1 { color: red; }');
        // Or a resource from fopen() or a stream from StreamInterface
        $files->addStream('logo.png', fopen(public_path('images/logo.png'), 'r'));
        // Or a file on disk (this is equivalent to using ->attach())
        $files->addFile('style.css', public_path('path/to/my/custom.css')));
    });
```

## Rendering

Now you've built your PDF you can either save it to your local disk, display it inline in the browser, trigger a download from the browser or use the stream as you wish.

The `Pdf` object also implements `Responsable` which will default to streaming the PDF as an inline attachment with the filename `download.pdf`.

> [!CAUTION]
> Every time you call one of these methods, the PDF will be re-rendered! The result of the last stream **is not re-used**. This can be potentially expensive depending on how complex your HTML, styling and how large your attached files are.

```php
use Savagescape\Pdf\Facades\Pdf;

$pdf = Pdf::fromHtml('<h1>Hello world</h1>');

// Store the PDF to disk
$pdf->store(storage_path('public/test.pdf'));

// Or, start a HTTP download
return $pdf->download('my-pdf-download.pdf');

// Or, display the PDF inline
return $pdf->inline('my-inline-pdf.pdf');

// Or, use the stream directly as you wish
$stream = $pdf->stream();

// Or, simply the return the Pdf object
// this is equivalent to $pdf->inline('download.pdf');
return $pdf;
```

---

## In-depth Examples
```php
use Savagescape\Pdf\Facades\Pdf;
use Savagescape\Pdf\Factory;
use Savagescape\Pdf\Options;
use Savagescape\Pdf\Files;

class MyController
{
    // Using the facade to load a plain HTML string
    // Set Landscape orientation
    // Set 50px margins
    // Use the "screen" media type
    // Attaching:
    // - storage/signatures/bob.jpg as signature.jpg
    // - public/images/logo.png as logo.png
    // - a inline string as style.css
    // - resources/pdf/footer.html as footer.html
    // Trigger a download with the default filename "your-contract.pdf"
    public function example1(Request $request)
    {
        $html = <<<HTML
        <link rel="stylesheet" href="style.css">
        <img src="logo.png">
        <h1 clas="something-important">Hello World</h1>
        <img src="signature.jpg">
        HTML>>>

        return Pdf::fromHtml($html)
            ->options(function (Options $options) {
                $options->landscape();
                $options->margins('50px');
                $options->emulatedMediaType('screen');
            })
            ->attach('signature.jpg', storage_path('signatures/bob.jpg'))
            ->files(function (Files $files) {
                $files->addFile('logo.png', public_path('images/logo.png'))
                $files->addString('style.css', '.something-important { color: red; }');
                $files->addStream('footer.html', fopen(resource_path('pdf/footer.html')));
            })
            ->download('your-contract.pdf');
    }

    // Using the factory directly, load a Blade template
    // include '$car' data in the Blade template
    // Attach several images from relations against the model into the PDF
    // Automatically generate a document outline
    // Include CSS background graphics
    // Save the pdf into the storage path
    public function example2(Factory $pdfFactory, Car $car)
    {
        $pdf = $pdfFactory->fromView('example', ['car' => $car]);

        $pdf->attach('manufacturer_logo.png', storage_path($car->manufacturer->logo->storage_filename));
        foreach ($car->photos as $photo) {
            $pdf->attach($photo->filename, storage_path($photo->storage_filename));
        }

        $pdf->options(
            fn (Options $options) => $options->generateDocumentOutline()->printBackground()
        );

        $pdf->save(storage_path("brochure/car-{$myModel->id}.pdf");
    }
}
```
