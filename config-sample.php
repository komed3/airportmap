<?php

    /* URLs */

    define( 'SITE', 'http' . ( $_SERVER['HTTPS'] == 'on' ? 's' : '' ) . '://' .
        $_SERVER['SERVER_NAME'] . '/' );

    /* paths */

    define( 'BASE', __DIR__ . '/' );
    define( 'PATH', BASE . 'includes/' );
    define( 'TEMPLATE', PATH . 'templates/' );
    define( 'LANG', BASE . 'languages/' );
    define( 'STATIC', BASE . 'static/' );

    /* Database connection */

    define( 'DB_HOST', '***' );
    define( 'DB_USER', '***' );
    define( 'DB_PASSWORD', '***' );
    define( 'DB_NAME', '***' );
    define( 'DB_PORT', '***' );
    define( 'DB_CHARSET', 'utf8' );
    define( 'DB_PREFIX', '' );

    /* defaults */

    define( 'COOKIE_EXP', time() + 7776000 );
    define( 'LOCALE', 'en-US' );

    /* API key */

    define( 'API_KEY', '***' );

?>