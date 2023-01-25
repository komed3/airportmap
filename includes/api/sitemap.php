<?php

    require_once __DIR__ . '/api.php';

    $sitemap = [];

    function sitemap_entry(
        string $site
    ) {

        global $sitemap, $sitemap_lang_tmp;

        $sitemap[] = '<url>
            <loc>' . SITE . $site . '</loc>
        </url>';

    }

    /* basic URLs */

    foreach( [
        '', 'airports', 'list', 'vicinity', 'weather',
        'weather/cat/VFR', 'weather/cat/MVFR', 'weather/cat/IFR',
        'weather/cat/LIFR', 'weather/cat/UNK', 'weather/sigmets',
        'stats', 'about', 'data', 'embed', 'privacy'
    ] as $site ) {

        sitemap_entry( $site );

    }

    /* list A-Z */

    foreach( str_split( '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ' ) as $letter ) {

        sitemap_entry( 'list/' . $letter );

    }

    /* airports */

    foreach( array_column( $DB->query( '
        SELECT   ICAO
        FROM     ' . DB_PREFIX . 'airport
        ORDER BY tier DESC
    ' )->fetch_all( MYSQLI_ASSOC ), 'ICAO' ) as $airport ) {

        sitemap_entry( 'airport/' . $airport . '/info' );
        sitemap_entry( 'airport/' . $airport . '/weather' );
        sitemap_entry( 'airport/' . $airport . '/nearby' );
        sitemap_entry( 'airport/' . $airport . '/radio' );
        sitemap_entry( 'airport/' . $airport . '/runways' );

    }

    /* regions */

    foreach( [ 'continent', 'country', 'region', 'ICAO' ] as $type ) {

        foreach( array_column( $DB->query( '
            SELECT  code
            FROM    ' . DB_PREFIX . $type . '
        ' )->fetch_all( MYSQLI_ASSOC ), 'code' ) as $region ) {

            sitemap_entry( 'airports/' . $type . '/' . $region );

        }

    }

    /* types & restrictions */

    foreach( [ 'type', 'restriction' ] as $type ) {

        foreach( array_column( $DB->query( '
            SELECT   ' . $type . ' AS col
            FROM     ' . DB_PREFIX . 'airport
            WHERE    ' . $type . ' IS NOT NULL
            GROUP BY ' . $type . '
        ' )->fetch_all( MYSQLI_ASSOC ), 'col' ) as $col ) {

            sitemap_entry( 'airports/' . $type . '/' . $col );

        }

    }

    /* timezones */

    foreach( array_column( $DB->query( '
        SELECT  ident
        FROM    ' . DB_PREFIX . 'timezone
    ' )->fetch_all( MYSQLI_ASSOC ), 'ident' ) as $tz ) {

        sitemap_entry( 'airports/timezone/' . $tz );

    }

    /* build index */

    $index = [];

    foreach( array_chunk( $sitemap, 10000 ) as $i => $chunk ) {

        $index[] = '<sitemap>
            <loc>' . SITE . 'sitemap-' . $i . '.xml</loc>
        </sitemap>';

        file_put_contents( BASE . 'sitemap-' . $i . '.xml', '<?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
            ' . implode( '', $chunk ) . '
        </urlset>' );

    }

    file_put_contents( BASE . 'sitemap.xml', '<?xml version="1.0" encoding="UTF-8"?>
    <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
        ' . implode( '', $index ) . '
    </sitemapindex>' );

    api_exit( [
        'urls' => count( $sitemap ),
        'files' => count( $index )
    ] );

?>