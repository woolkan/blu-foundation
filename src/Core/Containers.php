<?php
declare(strict_types=1);

namespace Blu\Foundation\Core;

use Psr\Container\ContainerInterface;
use Throwable;

class Containers
{
    private array $containers = [];

    public function __construct() {}

    public function addContainersPath(string $pattern): self
    {
        try {
            $containerDefinitionFiles = glob($pattern, GLOB_BRACE);
            foreach ($containerDefinitionFiles as $containerDefinitionFile) {
                $this->addContainerFile($containerDefinitionFile);
            }
        } catch (Throwable $e) {
            error_log('Błąd podczas wczytywania definicji kontenera: ' . $e->getMessage());
        }
        return $this;
    }

    private function addContainerFile(string $filePath): void
    {
        try {
            if (file_exists($filePath)) {
                $definitions = require $filePath;
                foreach ($definitions as $key => $value) {
                    if (isset($this->containers[$key])) {
                        error_log("Kolizja klucza kontenera: $key. Nadpisanie istniejącej definicji.");
                    }
                    $this->containers[$key] = $value;
                }
            }
        } catch (Throwable $e) {
            error_log('Błąd podczas wczytywania pliku konfiguracji kontenera: ' . $e->getMessage());
        }
    }

    public function init(ContainerInterface $container): ContainerInterface
    {
        foreach ($this->containers as $key => $value) {
            try {
                $container->set($key, $value);
            } catch (Throwable $e) {
                error_log('Błąd podczas dodawania definicji do kontenera: ' . $e->getMessage());
            }
        }
        return $container;
    }
}