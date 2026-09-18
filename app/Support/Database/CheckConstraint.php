<?php

declare(strict_types=1);

namespace App\Support\Database;

use BackedEnum;
use Illuminate\Support\Facades\DB;

/**
 * Helpers for the PostgreSQL CHECK constraints that Laravel's schema builder
 * cannot express. Used only from migrations.
 */
final class CheckConstraint
{
    public static function add(string $table, string $name, string $expression): void
    {
        DB::statement(sprintf('ALTER TABLE %s ADD CONSTRAINT %s CHECK (%s)', $table, $name, $expression));
    }

    public static function drop(string $table, string $name): void
    {
        DB::statement(sprintf('ALTER TABLE %s DROP CONSTRAINT IF EXISTS %s', $table, $name));
    }

    /**
     * Constrain a string column to the backing values of a PHP enum, so the
     * allowed values are defined once, in the enum.
     *
     * @param  class-string<BackedEnum>  $enum
     */
    public static function enum(string $table, string $column, string $enum): void
    {
        $values = implode(', ', array_map(
            static fn (BackedEnum $case): string => sprintf("'%s'", $case->value),
            $enum::cases(),
        ));

        self::add($table, self::enumName($table, $column), sprintf('%s IN (%s)', $column, $values));
    }

    public static function enumName(string $table, string $column): string
    {
        return sprintf('%s_%s_check', $table, $column);
    }
}
