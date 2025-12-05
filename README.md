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
-   **`format(string $format)`**: Set the output format to `'svg'` (default) or `'png'`.
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
-   *Note: Using `logo` automatically sets the error correction level to `'H'` (High).*

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

## License

The MIT License (MIT).
