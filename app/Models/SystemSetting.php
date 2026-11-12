<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[Fillable(['key', 'value'])]
class SystemSetting extends Model
{
    public const DEFAULT_MIN_INITIAL_DEPOSIT = 10000;

    public const DEFAULT_DAILY_WITHDRAW_LIMIT = 500000;

    /**
     * Retrieve an integer setting value with a fallback default.
     */
    public static function getInt(string $key, int $default): int
    {
        $value = static::query()->where('key', $key)->value('value');

        if ($value === null || $value === '') {
            return $default;
        }

        return (int) $value;
    }

    /**
     * Retrieve a boolean setting value with a fallback default.
     */
    public static function getBool(string $key, bool $default): bool
    {
        $value = static::query()->where('key', $key)->value('value');

        if ($value === null || $value === '') {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Persist multiple system setting key-value pairs atomically.
     *
     * @param  array<string, int|string|bool>  $values
     */
    public static function setValues(array $values): void
    {
        DB::transaction(function () use ($values): void {
            foreach ($values as $key => $value) {
                $serialized = is_bool($value) ? ($value ? '1' : '0') : (string) $value;

                static::query()->updateOrCreate(
                    ['key' => $key],
                    ['value' => $serialized]
                );
            }
        });
    }
}
