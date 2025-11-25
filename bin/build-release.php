#!/usr/bin/env php
<?php
// phpcs:ignoreFile -- This is a CLI build script, not executed in WordPress context

declare(strict_types=1);

use ZipArchive;

$root = dirname(__DIR__);
$distDir = $root . DIRECTORY_SEPARATOR . 'dist';
$buildDir = $distDir . DIRECTORY_SEPARATOR . 'fail2notify';

$options = getopt('', ['skip-composer']);
$runComposer = ! isset($options['skip-composer']);

if ($runComposer) {
    $cmd = 'set COMPOSER_MIRROR_PATH_REPOS=1 && composer install --no-dev --optimize-autoloader';
    if (stripos(PHP_OS_FAMILY, 'Windows') === false) {
        $cmd = 'COMPOSER_MIRROR_PATH_REPOS=1 composer install --no-dev --optimize-autoloader';
    }

    $cwd = getcwd();
    chdir($root);
    passthru($cmd, $code);
    chdir($cwd ?: $root);
    if (0 !== $code) {
        fwrite(STDERR, "Composer install failed.\n");
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI script, exit code is safe
        exit($code);
    }
}

if (is_dir($buildDir)) {
    rrmdir($buildDir);
}

if (! is_dir($distDir)) {
    mkdir($distDir, 0777, true);
}

mkdir($buildDir, 0777, true);

$excludes = [
    '.git',
    '.github',
    'bin',
    'dist',
    'node_modules',
    '.gitignore',
];

copy_dir($root, $buildDir, $excludes);

$zipTarget = $distDir . DIRECTORY_SEPARATOR . 'fail2notify.zip';
if (file_exists($zipTarget)) {
    unlink($zipTarget);
}

$zip = new ZipArchive();
if (true === $zip->open($zipTarget, ZipArchive::CREATE)) {
    add_dir_to_zip($zip, $buildDir, strlen($distDir) + 1);
    $zip->close();
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI script, file path is safe
    echo "Release build created at {$zipTarget}\n";
} else {
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI script, file path is safe
    fwrite(STDERR, "Unable to create {$zipTarget}\n");
    exit(1);
}

exit(0);

function copy_dir(string $source, string $destination, array $excludes = []): void
{
    $items = scandir($source) ?: [];
    foreach ($items as $item) {
        if (in_array($item, ['.', '..'], true)) {
            continue;
        }
        if (in_array($item, $excludes, true)) {
            continue;
        }

        $src = $source . DIRECTORY_SEPARATOR . $item;
        $dest = $destination . DIRECTORY_SEPARATOR . $item;

        if (is_dir($src)) {
            mkdir($dest, 0777, true);
            copy_dir($src, $dest, $excludes);
        } else {
            copy($src, $dest);
        }
    }
}

function rrmdir(string $dir): void
{
    if (! is_dir($dir)) {
        return;
    }
    $items = scandir($dir) ?: [];
    foreach ($items as $item) {
        if (in_array($item, ['.', '..'], true)) {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            rrmdir($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}

function add_dir_to_zip(ZipArchive $zip, string $dir, int $strip): void
{
    $items = scandir($dir) ?: [];
    foreach ($items as $item) {
        if (in_array($item, ['.', '..'], true)) {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        $relative = substr($path, $strip);
        // Convert Windows path separators to forward slashes for ZIP
        $relative = str_replace('\\', '/', $relative);
        if (is_dir($path)) {
            add_dir_to_zip($zip, $path, $strip);
        } else {
            $zip->addFile($path, $relative);
        }
    }
}
