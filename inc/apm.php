<?php

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
        int $limit = 100,
        int $offset = 0
    ) {

        global $DB;

        $word = strtolower( trim( $word ) );

        return $DB->query( '
            SELECT  *
            FROM    ' . DB_PREFIX . 'airport
            WHERE   ICAO LIKE "%' . $word . '%"
            OR      IATA LIKE "%' . $word . '%"
            OR      GPS LIKE "%' . $word . '%"
            OR      LOCAL LIKE "%' . $word . '%"
            OR      name LIKE "%' . $word . '%"
            LIMIT   ' . $offset . ', ' . $limit
        )->fetch_all( MYSQLI_ASSOC );

    }

?>