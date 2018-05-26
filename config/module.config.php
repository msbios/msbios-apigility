<?php
/**
 * @access protected
 * @author Judzhin Miles <info[woof-woof]msbios.com>
 */

namespace MSBios\Apigility;

use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'service_manager' => [
        'factories' => [
            \ZfrCors\Mvc\CorsRequestListener::class =>
                Factory\CorsRequestListenerFactory::class,
        ],
    ],

    'router' => [
        'routes' => [
            'oauth' => [
                'options' => [
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\AuthController::class =>
                Factory\AuthControllerFactory::class,
        ],
    ],

    'filters' => [
        'factories' => [
            Filter\CriteriaFilter::class =>
                InvokableFactory::class,
            Filter\DirectionFilter::class =>
                InvokableFactory::class
        ]
    ],

    'zf-oauth2' => [
        'storage_settings' => [
            'user_table' => 'tbl_users',
            'client_table' => 'oa2_clients',
            'access_token_table' => 'oa2_access_tokens',
            'refresh_token_table' => 'oa2_refresh_tokens',
            // 'code_table' => 'oa2AuthorizationCodes',
            // 'jwt_table' => 'oa2Jwt',
            // 'jti_table' => 'oa2Jti',
            'scope_table' => 'oa2_scopes',
            // 'public_key_table' => 'oa2PublicKeys',
        ],
    ],

    'zfr_cors' => [
        /**
         * Set the list of allowed origins domain with protocol.
         */
        'allowed_origins' => ['*'],

        /**
         * Set the list of HTTP verbs.
         */
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],

        /**
         * Set the list of headers. This is returned in the preflight request to indicate
         * which HTTP headers can be used when making the actual request
         */
        'allowed_headers' => [
            'Authorization',
            'Access-Control-Allow-Headers',
            'Content-Type',
            'X-Requested-With'
        ],

        /**
         * Set the max age of the preflight request in seconds. A non-zero max age means
         * that the preflight will be cached during this amount of time
         */
        // 'max_age' => 120,

        /**
         * Set the list of exposed headers. This is a whitelist that authorize the browser
         * to access to some headers using the getResponseHeader() JavaScript method. Please
         * note that this feature is buggy and some browsers do not implement it correctly
         */
        // 'exposed_headers' => [],

        /**
         * Standard CORS requests do not send or set any cookies by default. For this to work,
         * the client must set the XMLHttpRequest's "withCredentials" property to "true". For
         * this to work, you must set this option to true so that the server can serve
         * the proper response header.
         */
        // 'allowed_credentials' => false,
    ],
];
