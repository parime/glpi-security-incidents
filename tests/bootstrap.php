<?php

/**
 * PHPUnit bootstrap — same mechanism as the sibling plugin assetsign-glpi (same author): GLPI 11
 * has no lightweight bootstrap any more, `Glpi\Kernel\Kernel` (the same one `bin/console` uses) is
 * the real entry point, reproduced here to get a working DB connection and autoloading without an
 * HTTP context.
 *
 * Must run against a GLPI instance DEDICATED TO TESTS, never production, with this plugin
 * installed and active.
 *
 * GLPI_ROOT_DIR: absolute path to the GLPI root (the folder containing vendor/, src/,
 * bin/console...). Defaults to three levels above this file (tests/ -> plugin root -> plugins/ ->
 * <glpi>/), assuming this plugin lives at <glpi>/plugins/securityincidents/.
 */

$glpiRoot = getenv('GLPI_ROOT_DIR') ?: dirname(__DIR__, 3);

if (!is_file($glpiRoot . '/vendor/autoload.php')) {
    fwrite(
        STDERR,
        "GLPI not found at '$glpiRoot'.\n" .
        "Set the GLPI_ROOT_DIR environment variable to your GLPI installation root\n" .
        "(the folder containing vendor/, src/ and bin/console), e.g.:\n" .
        "  GLPI_ROOT_DIR=/var/www/glpi vendor/bin/phpunit\n"
    );
    exit(1);
}

require $glpiRoot . '/vendor/autoload.php';

if (is_file(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}

$kernel = new \Glpi\Kernel\Kernel('production');
$kernel->boot();

if (!\Plugin::isPluginActive('securityincidents')) {
    fwrite(
        STDERR,
        "The 'securityincidents' plugin is not installed/active on this test GLPI instance.\n" .
        "Install and activate it before running the tests (bin/console plugin:install securityincidents ; plugin:activate securityincidents).\n"
    );
    exit(1);
}
