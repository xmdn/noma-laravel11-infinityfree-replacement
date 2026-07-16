<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=UTF-8');
ini_set('display_errors', '1');
error_reporting(E_ALL);

$root = dirname(__DIR__);

register_shutdown_function(static function (): void {
    $error = error_get_last();

    if ($error !== null) {
        echo "\nSHUTDOWN ERROR\n";
        echo $error['message']."\n";
        echo $error['file'].':'.$error['line']."\n";
    }
});

echo "NOMA bootstrap check\n";
echo 'PHP: '.PHP_VERSION."\n\n";

$files = [
    'vendor/autoload.php',
    'vendor/composer/autoload_real.php',
    'vendor/composer/autoload_static.php',
    'vendor/composer/installed.php',
    'vendor/barryvdh/laravel-debugbar/src/ServiceProvider.php',
    'vendor/php-debugbar/php-debugbar/src/DebugBar.php',
    'vendor/php-debugbar/symfony-bridge/src/SymfonyHttpDriver.php',
    'bootstrap/app.php',
    'bootstrap/cache/packages.php',
];

foreach ($files as $file) {
    $path = $root.'/'.$file;
    echo sprintf(
        "%-75s exists=%s readable=%s size=%s\n",
        $file,
        file_exists($path) ? 'yes' : 'NO',
        is_readable($path) ? 'yes' : 'NO',
        is_file($path) ? (string) filesize($path) : '-'
    );
}

echo "\nWritable directories\n";

foreach (['storage', 'storage/logs', 'storage/framework', 'bootstrap/cache'] as $directory) {
    $path = $root.'/'.$directory;
    echo sprintf("%-25s exists=%s writable=%s\n", $directory, is_dir($path) ? 'yes' : 'NO', is_writable($path) ? 'yes' : 'NO');
}

try {
    echo "\nLoading Composer autoloader...\n";
    require $root.'/vendor/autoload.php';
    echo "Composer autoloader: OK\n";

    $classes = [
        Illuminate\Foundation\Application::class,
        Fruitcake\LaravelDebugbar\ServiceProvider::class,
        DebugBar\DebugBar::class,
        DebugBar\Bridge\Symfony\SymfonyHttpDriver::class,
    ];

    foreach ($classes as $class) {
        echo $class.': '.(class_exists($class) ? 'OK' : 'MISSING')."\n";
    }

    echo "\nLoading Laravel application...\n";
    $app = require $root.'/bootstrap/app.php';
    echo 'Application object: '.$app::class."\n";

    echo "Booting service providers...\n";
    $request = Illuminate\Http\Request::capture();
    $app->instance('request', $request);
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $kernel->bootstrap();
    echo "Laravel bootstrap: OK\n";
    echo 'Debugbar enabled config: '.var_export(config('debugbar.enabled'), true)."\n";
    echo 'Debugbar force allow config: '.var_export(config('debugbar.force_allow_enable'), true)."\n";
} catch (Throwable $exception) {
    echo "\nCAUGHT ".$exception::class."\n";
    echo $exception->getMessage()."\n";
    echo $exception->getFile().':'.$exception->getLine()."\n";
    echo $exception->getTraceAsString()."\n";
}

echo "\nDelete this diagnostic file after use.\n";
