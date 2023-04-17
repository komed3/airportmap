<?php

    require_once __DIR__ . '/api.php';

    $skip = array_filter( explode( ' ', file_get_contents( './wiki_skip.txt' ) ?? '' ) );

    $i = 0;

    $res = $DB->query( '
        SELECT   ICAO, wiki
        FROM     ' . DB_PREFIX . 'airport
        WHERE    wiki IS NOT NULL
        AND      ICAO NOT IN (
            "' . implode( '", "', $skip ) . '"
        )
        ORDER BY tier DESC,
                 ICAO ASC
        LIMIT    0, 25
    ' );

    while( $row = $res->fetch_object() ) {

        if( count( $wiki = explode( ':', $row->wiki ) ) == 2 ) {

            $links = [ [
                'lang' => $wiki[0],
                'link' => $wiki[1]
            ] ];

            try {

                $json = json_decode( file_get_contents(
                    "https://" . $wiki[0] . ".wikipedia.org/w/api.php?action=query&prop=langlinks&llprop=url&titles=" .
                        $wiki[1] . "&redirects=&format=json"
                ), true );

                if( count( $json['query']['pages'] ) > 0 ) {

                    $page = array_shift( $json['query']['pages'] );

                    if( count( $page['langlinks'] ) > 0 ) {

                        foreach( $page['langlinks'] as $link ) {

                            $links[] = [
                                'lang' => $link['lang'],
                                'link' => preg_replace( '/https:\/\/(.+)\.wikipedia\.org\/wiki\//', '', $link['url'] )
                            ];

                        }

                    }

                }

                if( count( $links ) > 0 ) {

                    echo 'INSERT INTO wiki (
                        airport, lang, link
                    ) VALUES ' . implode( ', ', array_map( function ( $l ) use ( $DB, $row ) {
                        return '( "' . $row->ICAO . '", "' .
                            $DB->real_escape_string( $l['lang'] ) . '", "' .
                            $DB->real_escape_string( $l['link'] ) . '" )';
                    }, $links ) ) . ';<br /><br />';

                    $i++;

                }

            } catch ( Throwable $e ) {

                file_put_contents( './wiki_error.txt', ' ' . $row->ICAO, FILE_APPEND );

                echo '# [ ERROR (' . $row->ICAO . ') // ' . $e->getMessage() . ' ]<br /><br />';

            }

        }

        file_put_contents( './wiki_skip.txt', ' ' . $row->ICAO, FILE_APPEND );

    }

    $DB->close();

?>