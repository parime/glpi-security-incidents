<?php

/**
 * Constants GLPI core defines at real runtime (front-controller), absent from a plain
 * `require vendor/autoload.php` used here as the PHPStan bootstrap (deliberately not the real GLPI
 * bootstrap, which requires a DB connection). Arbitrary values — only their existence matters for
 * static analysis, never their real content. Same pattern as the sibling plugin assetsign-glpi.
 */
if (!defined('GLPI_DOC_DIR')) {
    define('GLPI_DOC_DIR', '/tmp/glpi-phpstan-doc');
}
if (!defined('GLPI_TMP_DIR')) {
    define('GLPI_TMP_DIR', '/tmp/glpi-phpstan-tmp');
}
if (!defined('GLPI_PLUGIN_DOC_DIR')) {
    define('GLPI_PLUGIN_DOC_DIR', '/tmp/glpi-phpstan-plugin-doc');
}

foreach (['/var/www/glpi/vendor/autoload.php', '/var/www/html/glpi/vendor/autoload.php'] as $glpiAutoload) {
    if (is_file($glpiAutoload)) {
        require_once $glpiAutoload;
        break;
    }
}
