<?php
declare(strict_types=1);

namespace Blu\Foundation\View\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetVersionExtension extends AbstractExtension
{
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset_version', [$this, 'assetVersion']),
        ];
    }

    public function assetVersion(string $webPath): string
    {
        $filePath = $this->basePath . '/' . ltrim($webPath, '/');
        $version = file_exists($filePath) ? filemtime($filePath) : time();
        return $webPath . '?v=' . $version;
    }
}