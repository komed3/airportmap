<?php

    /* set defaults */

    date_default_timezone_set( 'UTC' );
    error_reporting( 0 );

    /* load config */

    require_once __DIR__ . '/../config.php';

    /* load required files */

    require_once PATH . 'language.php';
    require_once PATH . 'airport.php';
    require_once PATH . 'weather.php';
    require_once PATH . 'navaid.php';
    require_once PATH . 'content.php';
    require_once PATH . 'map.php';

    /* open DB connection */

    $DB = new Mysqli(
        DB_HOST,
        DB_USER, DB_PASSWORD,
        DB_NAME, DB_PORT
    );

    $DB->set_charset( DB_CHARSET );

    /* load language messages */

    i18n_load();

    /* airport count by type */

    define( 'AIRPORT_STATS', airport_count() );
    define( 'AIRPORT_ALL', array_sum( array_column( AIRPORT_STATS, 'cnt' ) ) );

    /* load basic resources */

    add_resource( 'leaflet', 'css', 'https://unpkg.com/leaflet@1.9.3/dist/leaflet.css' );
    add_resource( 'base', 'css', 'base.css' );

    add_resource( 'jquery', 'js', 'https://code.jquery.com/jquery-3.6.1.min.js' );
    add_resource( 'jquery-cookie', 'js', 'jquery.cookie.js' );
    add_resource( 'leaflet', 'js', 'https://unpkg.com/leaflet@1.9.3/dist/leaflet.js' );
    add_resource( 'base', 'js', 'base.js' );

    /* fetch path */

    $path = array_values( array_filter( explode( '/', $_SERVER['REQUEST_URI'] ) ) );

    /* load template */

    if( is_readable( TEMPLATE . ( $tpl = ( $path[0] . '_' . $path[1] ) ?? '' ) . '.php' ) ) {

        require_once TEMPLATE . $tpl . '.php';

    } else if( is_readable( TEMPLATE . ( $tpl = $path[0] ?? 'map' ) . '.php' ) ) {

        require_once TEMPLATE . $tpl . '.php';

    } else {

        require_once TEMPLATE . '404.php';

    }

    /* close DB connection */

    $DB->close();

?>