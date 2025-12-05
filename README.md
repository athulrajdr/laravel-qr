# Laravel QR Code Generator

A simple and flexible QR code generator for Laravel applications, based on `BaconQrCode`. This package allows you to generate QR codes with custom colors, sizes, styles, and embedded logos.

## Installation

This package is currently configured as a local package. Ensure your main `composer.json` includes the repository path:

```json
"repositories": [
    {
        "type": "path",
        "url": "./packages/athulr/laravel-qr"
    }
],
```

Then require the package:

```bash
composer require athulr/laravel-qr
```

## Basic Usage

To generate a simple QR code, use the `QrCode` facade:

```php
use Athulr\LaravelQr\Facades\QrCode;

// Returns an SVG string by default
return QrCode::generate('Hello World');
```

## Configuration

You can chain methods to configure the QR code:

```php
QrCode::size(500)
      ->margin(2)
      ->color('#00FF00') // Hex string
      ->backgroundColor(255, 255, 255) // RGB integers
      ->generate('Hello World');
```

### Available Methods

-   **`size(int $pixels)`**: Set the size of the QR code in pixels (default: 200).
-   **`margin(int $margin)`**: Set the white space margin around the QR code (default: 0).
-   **`format(string $format)`**: Set the output format to `'svg'` (default), `'png'`, `'jpg'`, or `'webp'`.
-   **`response(string $text)`**: Generates the QR code and returns a Laravel `Response` object with the correct `Content-Type` header.
-   **`color($r, $g = null, $b = null)`**: Set the foreground color. Accepts a hex string (e.g., `'#FF0000'`) or RGB integers.
-   **`backgroundColor($r, $g = null, $b = null)`**: Set the background color. Accepts a hex string or RGB integers.
-   **`errorCorrection(string $level)`**: Set the error correction level: `'L'` (Low), `'M'` (Medium), `'Q'` (Quartile), `'H'` (High).

## Advanced Styling

You can customize the shape of the modules (dots) and the eyes (finder patterns).

```php
QrCode::style('dot')
      ->eye('circle')
      ->generate('Styled QR Code');
```

-   **`style(string $style, float $intensity = null)`**: Set the module style.
    -   `'square'` (default)
    -   `'dot'`
    -   `'round'`
-   **`eye(string $style)`**: Set the style for both the eye frame and ball.
    -   `'square'` (default)
    -   `'circle'`

### Independent Eye Customization

You can customize the outer frame and the inner ball of the eyes independently:

```php
QrCode::eyeFrame('circle') // Circular outer frame
      ->eyeBall('square')  // Square inner ball
      ->generate('Custom Eye Styles');
```

-   **`eyeFrame(string $style)`**: Set the style of the outer eye frame.
-   **`eyeBall(string $style)`**: Set the style of the inner eye ball.

## Logo Embedding

You can embed a logo in the center of the QR code.
**Note:** This requires the `format` to be set to `'png'` and the `imagick` PHP extension to be installed.

```php
QrCode::format('png')
      ->logo(public_path('logo.png'), 30) // Path and size percentage (default 20%)
      ->generate('QR with Logo');
```

-   **`logo(string $path, int $percentage = 20)`**: Embeds an image at the given path. The `percentage` argument determines how much of the QR code width the logo should occupy.
-   *Note: Using `logo` requires a raster format (`png`, `jpg`, `webp`) and the `imagick` extension. It automatically sets error correction to `'H'`.*

## Saving to File

You can save the generated QR code directly to a file:

```php
QrCode::save('Hello World', storage_path('app/qr-code.svg'));
```

## Base64 Data URI

Get the QR code as a Base64 data URI (useful for embedding in `<img>` tags):

```php
$dataUri = QrCode::base64('Hello World');
// Output: data:image/svg+xml;base64,...
```

## HTTP Response

You can return the QR code directly as an HTTP response with the correct `Content-Type` header. This is useful for displaying the image directly in a browser or `<img>` tag source.

```php
Route::get('/qr-code', function () {
    return QrCode::size(500)
        ->format('webp')
        ->response('Hello World');
});
```

## Image Quality & Sharpness

For raster formats (`png`, `jpg`, `webp`), the package uses improved rendering with **anti-aliasing** and **supersampling** to ensure smooth edges on circular eyes and dot modules.

**Important:** To ensure sharpness, always generate the QR code at the size you intend to display it. For example, if you want to display a 1000px image, call `size(1000)`. Upscaling a small generated image (e.g., 200px displayed at 1000px) will result in blurriness.

```php
// Good: Generates a high-res, smooth image
QrCode::size(1000)->format('png')->generate('...');
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
