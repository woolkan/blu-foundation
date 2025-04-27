<?php
declare(strict_types=1);

namespace Blu\Foundation\View\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetExtension extends AbstractExtension
{
    private string $domain;
    
    public function __construct(string $domain)
    {
        $this->domain = rtrim($domain, '/'); // Usuwamy ewentualny ukośnik na końcu domeny
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset', [$this, 'assetFunction']),
        ];
    }

    public function assetFunction(string $path): string
    {
        return $this->domain . '/' . ltrim($path, '/'); // Dodajemy ukośnik, jeśli go brakuje
    }
}