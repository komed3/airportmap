<?php

    require_once __DIR__ . '/api.php';

    $skip = array_filter( explode( ' ', file_get_contents( './file_search_skip.txt' ) ?? '' ) );
    $base = API . 'file_search.php?api_key=' . API_KEY . '&search=';

    function wiki(
        string $query,
        string $lang = 'en'
    ) {

        return json_decode(
            file_get_contents( 'https://' . $lang . '.wikipedia.org/w/api.php?action=query&format=json&' . $query ),
            true
        )['query'];

    }

    function wiki_page(
        array $res
    ) {

        return array_shift( $res['pages'] );

    }

    if( empty( $_GET['search'] ) ) {

        $res = $DB->query( '
            SELECT   ICAO
            FROM     ' . DB_PREFIX . 'airport
            WHERE    ICAO NOT IN (
                SELECT  airport
                FROM    ' . DB_PREFIX . 'image
            )
            AND      ICAO NOT IN (
                "' . implode( '", "', $skip ) . '"
            )
            ORDER BY tier DESC
            LIMIT    0, 1
        ' );

        if( $res->num_rows ) {

            header( 'LOCATION: ' . $base . $res->fetch_object()->ICAO );
            exit;

        }

    } else {

        $search = trim( $_GET['search'] );
        $ICAO = trim( strtoupper( $_GET['ICAO'] ?? $search ) );

        // skip airport

        if( !empty( $_GET['skip'] ) ) {

            file_put_contents( './file_search_skip.txt', ' ' . $ICAO, FILE_APPEND );

            header( 'LOCATION: ' . $base );
            exit;

        }

        // save file

        if( !empty( $_POST['url'] ) ) {

            $DB->query( '
                INSERT INTO image (
                    airport, url, credits
                ) VALUES (
                    "' . $ICAO . '",
                    "' . base64_decode( $_POST['url'] ) . '",
                    "' . addslashes( base64_decode( $_POST['credits'] ) ) . '"
                )
            ' );

            header( 'LOCATION: ' . $base );
            exit;

        }

        echo '<h1>Search for <u>' . $search . '</u></h1>
        <a href="' . $base . $ICAO . '&skip=1"><b>SKIP AIRPORT!</b></a>';

        foreach( [ 'en', 'de', 'fr' ] as $lang ) {

            echo '<h2>Search in [' . $lang . '] …</h2>';

            if( !empty( $check = wiki( 'prop=pageprops&ppprop=disambiguation&titles=' . $search, $lang ) ) &&
                !empty( $check = wiki_page( $check ) ) ) {

                // missing page

                if( array_key_exists( 'missing', $check ) ) {

                    echo 'Missing page!';

                }

                // disambig page

                else if( array_key_exists( 'pageprops', $check ) ) {

                    echo 'Disambiguation:<ul>';

                    foreach( wiki( 'generator=links&gpllimit=max&titles=' . $search, $lang )['pages'] as $link ) {

                        if( $link['ns'] == 0 ) {

                            echo '<li><a href="' . $base . str_replace( ' ', '_', $link['title'] ) . '&ICAO=' .
                                $search . '">' . $link['title'] . '</a></li>';

                        }

                    }

                    echo '</ul>';

                }

                // fetch images

                else {

                    echo 'Images:';

                    if( !empty( $images = wiki_page( wiki( 'prop=images&imlimit=max&redirects=1&&titles=' . $search ) )['images'] ) ) {

                        foreach( $images as $img ) {

                            if( strpos( $img['title'], '.svg' ) === false &&
                                !empty( $res = wiki( 'prop=imageinfo&iiprop=url|user|size|extmetadata&titles=' .
                                    str_replace( ' ', '_', $img['title'] ), $lang ) ) &&
                                !empty( $res = wiki_page( $res ) ) ) {

                                $info = $res['imageinfo'][0];
                                $meta = $info['extmetadata'];

                                $credits = '<a href="' . $info['descriptionshorturl'] . '" target="_blank">' . $info['user'] . '</a> (' .
                                    date( 'Y', strtotime( $meta['DateTimeOriginal']['value'] ?? $meta['DateTime']['value'] ) ) . ') / ' .
                                    $meta['LicenseShortName']['value'] . ', via Wikimedia Commons';

                                echo '<form action="" method="post">
                                    <img src="' . $info['url'] . '" style="max-width: 320px; max-height: 260px;">
                                    <p><b>SIZE:</b> ' . $info['width'] . ' x ' . $info['height'] . 'px</p>
                                    <p><b>CREDITS: ' . $credits . '</b></p>
                                    <input type="hidden" name="url" value="' . base64_encode( $info['url'] ) . '" />
                                    <input type="hidden" name="credits" value="' . base64_encode( $credits ) . '" />
                                    <button type="submit">Take it!</button>
                                </form>';

                            }

                        }

                    } else {

                        echo ' <b>NONE</b>';

                    }

                }

            } else {

                echo 'ERROR occurred!';

            }

        }

    }

?>