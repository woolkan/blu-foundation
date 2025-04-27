<?php
declare(strict_types=1);

namespace Blu\Foundation\FieldConfig;

interface FieldConfigProviderInterface
{
    public function all(): array;
    public function get(string $field): array;
}