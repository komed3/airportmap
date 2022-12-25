<?php

    /* set defaults */

    date_default_timezone_set( 'UTC' );
    error_reporting( -1 );

    /* load config */

    require_once __DIR__ . '/../config.php';

    /* load required files */

    require_once PATH . 'language.php';

    /* open DB connection */

    $DB = new Mysqli(
        DB_HOST,
        DB_USER, DB_PASSWORD,
        DB_NAME, DB_PORT
    );

    $DB->set_charset( DB_CHARSET );

    /* load language messages */

    i18n_load();

    /* fetch path */

    $path = array_filter( explode( '/', $_SERVER['REQUEST_URI'] ) );

    /* load template */

    if( is_readable( TEMPLATE . ( $tpl = $path[0] ?? '' ) . '.php' ) ) {

        include_once TEMPLATE . $tpl . '.php';

    } else {

        include_once TEMPLATE . '404.php';

    }

    /* close DB connection */

    $DB->close();

?>