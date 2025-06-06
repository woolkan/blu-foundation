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

    /**
     * Adds a cache-busting version parameter to an asset URL.
     * Logs an error when the referenced file does not exist.
     */
    public function assetVersion(string $webPath): string
    {
        $filePath = $this->basePath . '/' . ltrim($webPath, '/');
        if (!file_exists($filePath)) {
            error_log('Asset not found: ' . $filePath);
            $version = time();
        } else {
            $version = filemtime($filePath);
        }
        return $webPath . '?v=' . $version;
    }
}