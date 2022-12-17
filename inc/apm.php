<?php

    if( defined( 'NO_TOKEN' ) || !isset( $_POST['token'] ) )
        die( 'Wrong entry point :(' );

    require_once __DIR__ . '/../config.php';

    $DB = new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT );

    $DB->set_charset( DB_CHARSET );

    function airport_stats() {

        global $DB;

        $stats = [];

        foreach( $DB->query( '
            SELECT   type,
                    COUNT( ICAO ) AS cnt
            FROM     ' . DB_PREFIX . 'airport
            GROUP BY type
        ' )->fetch_all() as $type ) {

            $stats[ $type[0] ] = $type[1];

        }

        return $stats;

    }

    function airport_search(
        string $word,
        int $limit = -1,
        int $offset = 0
    ) {

        global $DB;

        $word = strtolower( trim( $word ) );

        return $DB->query( '
            SELECT  *
            FROM    ' . DB_PREFIX . 'airport
            WHERE   CONVERT( ICAO USING utf8 ) LIKE "%' . $word . '%"
            OR      CONVERT( IATA USING utf8 ) LIKE "%' . $word . '%"
            OR      CONVERT( GPS USING utf8 ) LIKE "%' . $word . '%"
            OR      CONVERT( LOCAL USING utf8 ) LIKE "%' . $word . '%"
            OR      CONVERT( name USING utf8 ) LIKE "%' . $word . '%"
            ' . ( $limit >= 0
                ? 'LIMIT ' . $offset . ', ' . $limit
                : ''
            )
        )->fetch_all( MYSQLI_ASSOC );

    }

?>