<?php
declare(strict_types=1);

namespace Blu\Foundation\View\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ConstantsExtension extends AbstractExtension
{

    public function __construct(private readonly array $constants)
    {
    }

    /**
     * Rejestrujemy funkcje dostępne w szablonie Twig
     *
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_constant', [$this, 'getConstant']),
        ];
    }

    /**
     * Pobiera zagnieżdżoną wartość z tablicy na podstawie łańcucha kluczy, np.:
     * getConstant('menu.home') -> zwróci $constants['menu']['home']
     */
    public function getConstant(string $keys): mixed
    {
        // Podzielimy klucz po kropce (np. 'menu.home' -> ['menu', 'home'])
        $keysArray = explode('.', $keys);

        // Przechodzimy przez tablicę
        $value = $this->constants;
        foreach ($keysArray as $key) {
            if (!isset($value[$key])) {
                // Jeśli nie ma klucza, zwróć null (lub możesz rzucić wyjątek)
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }
}