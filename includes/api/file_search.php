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
            AND      LENGTH( ICAO ) = 4
            AND      CONVERT( ICAO USING utf8 ) REGEXP "[A-Z]{4}"
            ORDER BY ICAO ASC
            LIMIT    0, 1
        ' );

        if( $res->num_rows ) {

            header( 'LOCATION: ' . $base . $res->fetch_object()->ICAO );
            exit;

        }

    } else {

        $search = trim( $_GET['search'] );
        $ICAO = trim( strtoupper( $_GET['ICAO'] ?? $search ) );

        // red list

        if( !empty( $_GET['redlist'] ) ) {

            file_put_contents( './file_search_redlist.txt', ' ' . $ICAO, FILE_APPEND );

        }

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

        ?>
<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="UTF-8" />
        <title>APM API :: FILE SEARCH</title>
        <style>

            body {
                margin: 20px;
                font-family: sans-serif;
            }

            h1 {
                margin: 0 0 20px 0;
                font-size: 26px;
                font-weight: bold;
            }

            h2 {
                margin: 40px 0 20px 0;
                font-size: 20px;
                font-weight: bold;
            }

            code {
                padding: 1px 5px;
                background: #e5e5e5;
                border-radius: 4px;
            }

            a:link, a:visited, a:hover {
                text-decoration: none;
                color: #0047ab;
            }

            button,
            .skip:link, .skip:visited, .skip:hover,
            .redl:link, .redl:visited, .redl:hover {
                display: inline-block;
                padding: 4px 8px;
                text-decoration: none;
                font-size: 18px;
                font-weight: bold;
                color: #ffffff;
                background: #0047ab;
                border: 0;
                border-radius: 4px;
                cursor: pointer;
            }

            .redl:link, .redl:visited, .redl:hover {
                background: #ee4b2b;
            }

            .error {
                font-size: 18px;
                font-weight: bold;
                color: #ee4b2b;
            }

            .grid {
                column-count: 3;
                column-gap: 30px;
            }

            .grid .image {
                break-inside: avoid;
                margin-bottom: 30px;
                padding: 10px;
                border: 2px solid #e5e5e5;
                border-radius: 10px;
            }

            .grid .image img {
                width: 100%;
                height: auto;
            }

            .grid .image p {
                margin: 10px 0;
            }

        </style>
    </head>
    <body>
        <h1>Search for: <code><?php echo str_replace( '_', ' ', $search ); ?></code></h1>
        <a href="<?php echo $base . $ICAO; ?>&skip=1" class="skip">Skip this Airport!</a>
        <a href="<?php echo $base . $ICAO; ?>&skip=1&redlist=1" class="redl">Red List!</a>
        <?php

        foreach( [ 'en', 'de', 'fr' ] as $lang ) {

            ?><h2>Search in <code>wiki::<?php echo $lang; ?></code> …</h2><?php

            if( !empty( $check = wiki( 'prop=pageprops&ppprop=disambiguation&titles=' . $search, $lang ) ) &&
                !empty( $check = wiki_page( $check ) ) ) {

                // missing page

                if( array_key_exists( 'missing', $check ) ) {

                    ?><p class="error">Missing page!</p><?php

                }

                // disambig page

                else if( array_key_exists( 'pageprops', $check ) ) {

                    ?><b>Disambiguation:</b><ul><?php

                    foreach( wiki( 'generator=links&gpllimit=max&titles=' . $search, $lang )['pages'] as $link ) {

                        if( $link['ns'] == 0 ) {

                            ?><li><a href="<?php echo $base . str_replace( ' ', '_', $link['title'] ); ?>&ICAO=<?php echo $search; ?>">
                                <?php echo $link['title']; ?></a>
                            </li><?php

                        }

                    }

                    ?></ul><?php

                }

                // fetch images

                else {

                    $has_images = false;

                    if( !empty( $images = wiki_page( wiki( 'prop=images&imlimit=max&redirects=1&&titles=' . $search, $lang ) )['images'] ) ) {

                        ?><div class="grid"><?php

                        foreach( $images as $img ) {

                            if( !in_array( $img['title'], [
                                    'File:Aviacionavion.png'
                                ] ) &&
                                strpos( $img['title'], '.svg' ) === false &&
                                !empty( $res = wiki( 'prop=imageinfo&iiprop=url|user|size|mime|extmetadata&titles=' .
                                    str_replace( ' ', '_', $img['title'] ), $lang ) ) &&
                                !empty( $res = wiki_page( $res ) ) ) {

                                $info = $res['imageinfo'][0];
                                $meta = $info['extmetadata'];

                                if( strpos( $info['mime'], 'image' ) !== false ) {

                                    $credits = '<a href="' . $info['descriptionshorturl'] . '" target="_blank">' . $info['user'] . '</a> (' .
                                        date( 'Y', strtotime( $meta['DateTimeOriginal']['value'] ?? $meta['DateTime']['value'] ) ) . '), ' .
                                        $meta['LicenseShortName']['value'] . ', via Wikimedia Commons';

                                    ?><form action="" method="post" class="image">
                                        <img src="<?php echo $info['url']; ?>">
                                        <p><b>TITLE:</b> <?php echo $img['title']; ?></p>
                                        <p><b>MIME TYPE:</b> <?php echo $info['mime']; ?></p>
                                        <p><b>SIZE:</b> <?php echo $info['width']; ?> x <?php echo $info['height']; ?>px</p>
                                        <p><b>CREDITS:</b> <?php echo $credits; ?></p>
                                        <input type="hidden" name="url" value="<?php echo base64_encode( $info['url'] ); ?>" />
                                        <input type="hidden" name="credits" value="<?php echo base64_encode( $credits ); ?>" />
                                        <button type="submit">Take it!</button>
                                    </form><?php

                                    $has_images = true;

                                }

                            }

                        }

                        ?></div><?php

                    }

                    if( !$has_images ) {

                        ?><p class="error">Could not found any images!</p><?php

                    }

                }

            } else {

                ?><p class="error">ERROR occurred!</p><?php

            }

        }

        ?>
    </body>
</html>
        <?php

    }

?>