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

    /* load requirements */

    function load_requirements(
        string ...$files
    ) {

        foreach( $files as $file ) {

            if( is_readable( $path = PATH . $file . '.php' ) ) {

                require_once $path;

            } else return false;

        }

        return true;

    }

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

        global $DB;

        api_output( $content );

        $DB->close();

        exit;

    }

    /* get file from URL */

    function api_url2file(
        string $url
    ) {

        fopen( 'cookies.txt', 'w' );

        $ch = curl_init();

        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 0 );
        curl_setopt( $ch, CURLOPT_COOKIESESSION, true );
    
        curl_setopt( $ch, CURLOPT_COOKIEFILE, 'cookies.txt' );
        curl_setopt( $ch, CURLOPT_COOKIEJAR, 'cookies.txt' );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, [
            'GET /1575051 HTTP/1.1',
            'Host: ' . parse_url( $url )['host'],
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.8',
            'Cache-Control: max-age=0',
            'Connection: keep-alive',
            'Host: adfoc.us',
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.116 Safari/537.36',
        ] );

        $result = curl_exec( $ch );

        curl_close( $ch );

        return $result;

    }

?>