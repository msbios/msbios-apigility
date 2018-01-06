<?php
/**
 * File generated by vendor/bin/apigility-upgrade-to-1.5.
 *
 * Original contents are available in config/development.config.php.dist.old
 */

return [
    'modules' => [
        'ZendDeveloperTools',
        'ZF\\Apigility\\Admin',
        'ZF\\Apigility\\Documentation\\Swagger',
    ],
    'module_listener_options' => [
        'config_glob_paths' => [
            __DIR__ . '/autoload/{,*.}{global,local}-development.php',
        ],
        'config_cache_enabled' => false,
        'module_map_cache_enabled' => false,
    ],
];