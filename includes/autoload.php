<?php
declare(strict_types=1);

/**
 * Minimal PSR-4-ish autoloader for the LastJob namespace -> includes/.
 * No Composer dependency required for the engine itself.
 */
spl_autoload_register(static function (string $class): void {
    $prefix = 'LastJob\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});
