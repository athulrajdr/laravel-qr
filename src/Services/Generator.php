<?php

namespace Athulr\LaravelQr\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\Module\DotsModule;
use BaconQrCode\Renderer\Module\RoundnessModule;
use BaconQrCode\Renderer\Module\SquareModule;
use BaconQrCode\Renderer\Eye\SimpleCircleEye;
use BaconQrCode\Renderer\Eye\SquareEye;
use BaconQrCode\Renderer\Eye\CompositeEye;
use Athulr\LaravelQr\Services\Eyes\CircleEye;
use Athulr\LaravelQr\Services\Backends\CustomImagickImageBackEnd;
use BaconQrCode\Writer;

class Generator
{
    protected $config;
    protected $size;
    protected $margin;
    protected $format;
    protected $foreground;
    protected $background;
    protected $errorCorrection;
    protected $moduleStyle;
    protected $moduleIntensity;
    protected $eyeFrameStyle;
    protected $eyeBallStyle;
    protected $logo;
    protected $logoSize;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->size = $config['size'] ?? 200;
        $this->margin = $config['margin'] ?? 0;
        $this->format = $config['format'] ?? 'svg';
        $this->foreground = $config['foreground'] ?? [0, 0, 0];
        $this->background = $config['background'] ?? [255, 255, 255];
        $this->errorCorrection = $config['error_correction'] ?? 'L';
        $this->moduleStyle = 'square';
        $this->eyeFrameStyle = 'square';
        $this->eyeBallStyle = 'square';
    }
    public function size(int $pixels)
    {
        $this->size = $pixels;
        return $this;
    }

    public function margin(int $margin)
    {
        $this->margin = $margin;
        return $this;
    }

    public function format(string $format)
    {
        $this->format = $format;
        return $this;
    }

    public function color($r, $g = null, $b = null)
    {
        if (is_string($r)) {
            $this->foreground = $this->hexToRgb($r);
        } else {
            $this->foreground = [$r, $g, $b];
        }
        return $this;
    }

    public function backgroundColor($r, $g = null, $b = null)
    {
        if (is_string($r)) {
            $this->background = $this->hexToRgb($r);
        } else {
            $this->background = [$r, $g, $b];
        }
        return $this;
    }

    private function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        return [$r, $g, $b];
    }

    public function errorCorrection(string $level)
    {
        $this->errorCorrection = $level;
        return $this;
    }

    public function style(string $style, float $intensity = null)
    {
        $this->moduleStyle = $style;
        $this->moduleIntensity = $intensity;
        return $this;
    }

    public function eye(string $style)
    {
        $this->eyeFrameStyle = $style;
        $this->eyeBallStyle = $style;
        return $this;
    }

    public function eyeFrame(string $style)
    {
        $this->eyeFrameStyle = $style;
        return $this;
    }

    public function eyeBall(string $style)
    {
        $this->eyeBallStyle = $style;
        return $this;
    }

    public function logo(string $path, int $percentage = 20)
    {
        $this->logo = $path;
        $this->logoSize = $percentage;
        $this->errorCorrection = 'H'; // Auto-set high error correction
        return $this;
    }

    // ... logo method ...

    private function getModule()
    {
        switch ($this->moduleStyle) {
            case 'dot':
                return new DotsModule($this->moduleIntensity ?? DotsModule::MEDIUM);
            case 'round':
                return new RoundnessModule($this->moduleIntensity ?? RoundnessModule::MEDIUM);
            default:
                return SquareModule::instance();
        }
    }

    private function getEyePart($style)
    {
        switch ($style) {
            case 'circle':
                return CircleEye::instance();
            default:
                return SquareEye::instance();
        }
    }

    private function getEye()
    {
        return new CompositeEye(
            $this->getEyePart($this->eyeFrameStyle),
            $this->getEyePart($this->eyeBallStyle)
        );
    }

    private function renderer()
    {
        $backend = $this->format === 'svg'
            ? new SvgImageBackEnd()
            : new CustomImagickImageBackEnd();

        $color = new Rgb(...$this->foreground);
        $backgroundColor = new Rgb(...$this->background);

        $fill = Fill::uniformColor($backgroundColor, $color);

        return new ImageRenderer(
            new RendererStyle(
                $this->size,
                $this->margin,
                $this->getModule(),
                $this->getEye(),
                $fill
            ),
            $backend
        );
    }

    public function generate($text)
    {
        $writer = new Writer($this->renderer());
        $content = $writer->writeString($text, $this->config['encoding'] ?? 'UTF-8', ErrorCorrectionLevel::valueOf($this->errorCorrection));

        if ($this->format === 'svg') {
            return $content;
        }

        if (class_exists('Imagick')) {
            $qr = new \Imagick();
            $qr->readImageBlob($content);
            $qr->setImageFormat($this->format);

            if (in_array($this->format, ['jpg', 'jpeg'])) {
                $bg = new \Imagick();
                $bg->newImage($qr->getImageWidth(), $qr->getImageHeight(), 'white');
                $bg->setImageFormat($this->format);
                $bg->compositeImage($qr, \Imagick::COMPOSITE_OVER, 0, 0);
                $qr = $bg;
            }

            if ($this->logo) {
                $this->applyLogo($qr);
            }

            return $qr->getImageBlob();
        }

        return $content;
    }

    protected function applyLogo(\Imagick $qr)
    {
        $qrWidth = $qr->getImageWidth();
        $qrHeight = $qr->getImageHeight();

        $logo = new \Imagick($this->logo);
        
        $logoWidth = $qrWidth * ($this->logoSize / 100);
        $logo->scaleImage($logoWidth, 0);
        
        // Center the logo
        $x = ($qrWidth - $logo->getImageWidth()) / 2;
        $y = ($qrHeight - $logo->getImageHeight()) / 2;

        $qr->compositeImage($logo, \Imagick::COMPOSITE_OVER, $x, $y);
    }

    public function save($text, $path)
    {
        file_put_contents($path, $this->generate($text));
        return $path;
    }

    public function base64($text)
    {
        return 'data:image/' . $this->format . ';base64,' . base64_encode($this->generate($text));
    }
    public function response($text)
    {
        $content = $this->generate($text);
        
        $mimeTypes = [
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
        ];

        $type = $mimeTypes[$this->format] ?? 'image/png';

        return response($content)->header('Content-Type', $type);
    }
}
