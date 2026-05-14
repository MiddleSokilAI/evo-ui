<?php

declare(strict_types=1);

/**
 * EvoUI analyses package code outside a fully booted Laravel application.
 * These helper signatures model the runtime contract narrowly enough for
 * package-level static analysis without changing production behavior.
 *
 * @param mixed $value
 */
function collect($value = null): mixed
{
}

/**
 * @param string|null $key
 * @param mixed $replace
 * @param string|null $locale
 */
function __($key = null, $replace = [], $locale = null): string
{
}
