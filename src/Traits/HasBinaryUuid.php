<?php
declare(strict_types=1);

namespace Blu\Foundation\Traits;

use Ramsey\Uuid\Uuid;

trait HasBinaryUuid
{
    public static function bootHasBinaryUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $uuid = Uuid::uuid4();
                $model->{$model->getKeyName()} = $uuid->toString();
            }
        });
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($key === $this->getKeyName() && is_string($value) && strlen($value) === 16) {
            return Uuid::fromBytes($value)->toString(); // BINARY(16) → UUID string
        }

        return $value;
    }

    public function setAttribute($key, $value)
    {
        if ($key === $this->getKeyName() && is_string($value) && strlen($value) === 36) {
            $value = Uuid::fromString($value)->toString(); // UUID string → BINARY(16)
        }

        return parent::setAttribute($key, $value);
    }
}