<?php

declare(strict_types=1);

namespace Blu\Foundation\Validation;

use InvalidArgumentException;

/**
 * Walidator danych formularza z obsługą reguł domyślnych, niestandardowych oraz komunikatów.
 */
class FormValidator
{
    /** @var array<string, callable> Niestandardowe reguły walidacji */
    private array $customRules = [];

    public function __construct()
    {
        // Domyślna rejestracja reguł
        $this->registerCustomRule('strong_password', function ($value): bool|string {
            return preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $value)
                ? true
                : 'Hasło musi zawierać co najmniej jedną wielką literę i cyfrę.';
        });

        $this->registerCustomRule('pl_phone', function ($value): bool|string {
            return preg_match('/^\+48[0-9]{9}$/', $value)
                ? true
                : 'Numer telefonu musi być w formacie +48XXXXXXXXX.';
        });
    }

    /**
     * Rejestruje niestandardową regułę.
     *
     * @param string   $name     Nazwa reguły (bez prefixu custom:)
     * @param callable $callback Funkcja przyjmująca wartość (oraz opcjonalnie cały zestaw danych) zwracająca true lub komunikat błędu
     */
    public function registerCustomRule(string $name, callable $callback): void
    {
        $this->customRules[$name] = $callback;
    }

    /**
     * Waliduje dane zgodnie z podanymi regułami i komunikatami.
     *
     * @param array<string,mixed> $data     Dane formularza ['pole' => wartość]
     * @param array<string,array<int,string>> $rules    Reguły walidacji ['pole' => ['required','numeric',...]]
     * @param array<string,array<string,string>> $messages Komunikaty ['pole' => ['required' => '...', 'numeric'=>'...', ...]]
     *
     * @throws InvalidArgumentException W przypadku niezalecenia reguły
     */
    public function validate(array $data, array $rules, array $messages = []): void
    {
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                // Rozbicie custom:, regex:, min:, max:, in:, confirmed_with: etc.
                [$ruleName, $ruleParam] = $this->parseRule($rule);

                $passes = match ($ruleName) {
                    'required' => $this->validateRequired($value),
                    'email'    => filter_var((string)$value, FILTER_VALIDATE_EMAIL) !== false,
                    'alpha'    => preg_match('/^[a-zA-Z]+$/', (string)$value),
                    'alpha_num' => preg_match('/^[a-zA-Z0-9]+$/', (string)$value),
                    'numeric'  => is_numeric($value),
                    'boolean'  => in_array($value, [true, false, 'true','false',0,1,'0','1'], true),
                    'date'     => strtotime((string)$value) !== false,
                    'url'      => filter_var((string)$value, FILTER_VALIDATE_URL) !== false,
                    'custom'   => $this->applyCustomRule($ruleParam, $value, $data),
                    'regex'    => preg_match($ruleParam, (string)$value),
                    'min'      => is_numeric($value) && (float)$value >= (float)$ruleParam,
                    'max'      => is_numeric($value) && (float)$value <= (float)$ruleParam,
                    'length'   => mb_strlen((string)$value) === (int)$ruleParam,
                    'in'       => in_array((string)$value, explode(',', $ruleParam), true),
                    'confirmed' => isset($data[$field.'_confirmation']) && $data[$field.'_confirmation'] === $value,
                    'confirmed_with' => isset($data[$ruleParam]) && $data[$ruleParam] === $value,
                    default     => throw new InvalidArgumentException("Nieznana reguła walidacji: {$ruleName}.")
                };

                if ($passes !== true) {
                    // Obsługa komunikatów: najpierw zdefiniowany komunikat w $messages, potem domyślny z custom albo standardowy
                    $message = $messages[$field][$ruleName]
                        ?? (is_string($passes) ? $passes : $this->defaultMessage($field, $ruleName, $ruleParam));
                    throw new InvalidArgumentException($message);
                }
            }
        }
    }

    /** Parsuje regułę na nazwę i parametr (np. "min:3" => ["min","3"]) */
    private function parseRule(string $rule): array
    {
        if (str_starts_with($rule, 'custom:')) {
            return ['custom', substr($rule, 7)];
        }
        if (str_starts_with($rule, 'regex:')) {
            return ['regex', substr($rule, 6)];
        }
        foreach (['min:','max:','length:','in:','confirmed_with:'] as $prefix) {
            if (str_starts_with($rule, $prefix)) {
                return [rtrim($prefix, ':'), substr($rule, strlen($prefix))];
            }
        }
        return [$rule, ''];
    }

    /** Walidacja pola "required" */
    private function validateRequired(mixed $value): bool
    {
        return !(empty($value) && $value !== '0');
    }

    /** Wykonuje niestandardową regułę walidacji */
    private function applyCustomRule(string $name, mixed $value, array $data): bool|string
    {
        if (!isset($this->customRules[$name])) {
            throw new InvalidArgumentException("Nieznana reguła walidacji: custom:{$name}.");
        }
        return call_user_func($this->customRules[$name], $value, $data);
    }

    /** Domyślne komunikaty dla reguł gdy brak zdefiniowanego w messages */
    private function defaultMessage(string $field, string $rule, string $param): string
    {
        return match ($rule) {
            'required' => "Pole '{$field}' jest wymagane.",
            'email'    => "Nieprawidłowy adres e-mail w polu '{$field}'.",
            'alpha'    => "Pole '{$field}' może zawierać tylko litery.",
            'alpha_num'=> "Pole '{$field}' może zawierać tylko litery i cyfry.",
            'numeric'  => "Pole '{$field}' musi być liczbą.",
            'boolean'  => "Pole '{$field}' musi być wartością logiczną.",
            'date'     => "Pole '{$field}' musi zawierać prawidłową datę.",
            'url'      => "Pole '{$field}' musi zawierać prawidłowy adres URL.",
            'regex'    => "Pole '{$field}' ma nieprawidłowy format.",
            'min'      => "Pole '{$field}' musi być większe lub równe {$param}.",
            'max'      => "Pole '{$field}' musi być mniejsze lub równe {$param}.",
            'length'   => "Pole '{$field}' musi mieć dokładnie {$param} znaków.",
            'in'       => "Pole '{$field}' musi zawierać jedną z wartości: {$param}.",
            'confirmed' => "Pole '{$field}' musi być potwierdzone.",
            'confirmed_with' => "Pole '{$field}' musi być zgodne z polem '{$param}'.",
            default    => "Pole '{$field}' nie przeszło walidacji regułą '{$rule}'.",
        };
    }
}
