<?php

    /* set defaults */

    date_default_timezone_set( 'UTC' );
    error_reporting( 0 );

    define( 'API_TIME', microtime( true ) );

    /* load config */

    require_once __DIR__ . '/../../config.php';

    /* auth api access */

    if( !array_key_exists( 'token', $_POST ) &&
        API_KEY != strtolower( trim( $_GET['api_key'] ?? '' ) ) ) {

        api_exit( [
            'error' => 'auth',
            'msg' => 'Access denied. API key is required.'
        ] );

    }

    /* define script is in API mode */

    define( 'API', true );

    /* open DB connection */

    $DB = new Mysqli(
        DB_HOST,
        DB_USER, DB_PASSWORD,
        DB_NAME, DB_PORT
    );

    $DB->set_charset( DB_CHARSET );

    /* api output */

    function api_output(
        array $content = []
    ) {

        echo json_encode( [
            'request' => $_SERVER['REQUEST_URI'],
            'status' => http_response_code(),
            'time' => microtime( true ) - API_TIME,
            'response' => $content
        ], JSON_NUMERIC_CHECK );

    }

    function api_exit(
        array $content = []
    ) {

        api_output( $content );

        $DB->close();

        exit;

    }

?>