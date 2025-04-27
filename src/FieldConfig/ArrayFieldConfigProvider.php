<?php
declare(strict_types=1);

namespace Blu\Foundation\FieldConfig;

class ArrayFieldConfigProvider implements FieldConfigProviderInterface
{
    public function __construct(protected array $config)
    {
    }

    public function all(): array
    {
        return $this->config;
    }

    public function get(string $field): array
    {
        return $this->config[$field] ?? [];
    }

    /**
     * Scalanie danych wejściowych do konfiguracji.
     *
     * @param array|null $data Dane do wstawienia (['klucz' => wartość])
     * @param string     $property Nazwa właściwości w config ('value' lub 'errors')
     * @param bool       $append   Czy doklejać (true = tablica błędów), czy nadpisywać (false = wartość)
     */
    protected function mergeConfig(?array $data, string $property, bool $append = false): void
    {
        if (empty($data)) {
            return;
        }

        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $this->config)) {
                continue;
            }

            if ($append) {
                $this->config[$key][$property] = $this->config[$key][$property] ?? [];
                $this->config[$key][$property][] = $value;
            } else {
                $this->config[$key][$property] = $value;
            }
        }
    }
}