<?php

    require_once __DIR__ . '/api.php';

    $sitemap_lang_tmp = implode( '', array_map( function( $lang ) {
        return '<xhtml:link rel="alternate" hreflang="' . $lang . '" href="' . SITE . $lang . '/$" />';
    }, LANGUAGES ) );

    $sitemap = [];

    function sitemap_entry(
        string $site,
        float $prior = 0.9,
        string $freq = 'daily'
    ) {

        global $sitemap, $sitemap_lang_tmp;

        $sitemap[] = '<url>
            <loc>' . SITE . $site . '</loc>
            <changefreq>' . $freq . '</changefreq>
            <priority>' . $prior . '</priority>
            ' . str_replace( '$', $site, $sitemap_lang_tmp ) . '
        </url>';

    }

    /* basic URLs */

    foreach( [
        '' => 1,
        'airports' => 1,
        'weather' => 1,
        'weather/cat/VFR' => 0.9,
        'weather/cat/MVFR' => 0.9,
        'weather/cat/IFR' => 0.9,
        'weather/cat/LIFR' => 0.9,
        'weather/cat/UNK' => 0.9,
        'weather/sigmets' => 1,
        'stats' => 1,
        'about' => 0.9,
        'data' => 0.7,
        'embed' => 0.7,
        'privacy' => 0.5
    ] as $site => $prior ) {

        sitemap_entry( $site, $prior, 'daily' );

    }

    /* airports */

    foreach( array_column( $DB->query( '
        SELECT   ICAO
        FROM     ' . DB_PREFIX . 'airport
        ORDER BY tier DESC
    ' )->fetch_all( MYSQLI_ASSOC ), 'ICAO' ) as $airport ) {

        sitemap_entry( 'airport/' . $airport . '/info', 0.9, 'monthly' );
        sitemap_entry( 'airport/' . $airport . '/weather', 0.9, 'hourly' );
        sitemap_entry( 'airport/' . $airport . '/nearby', 0.8, 'weekly' );
        sitemap_entry( 'airport/' . $airport . '/radio', 0.7, 'monthly' );
        sitemap_entry( 'airport/' . $airport . '/runways', 0.7, 'monthly' );

    }

    /* regions */

    foreach( [ 'continent', 'country', 'region', 'ICAO' ] as $type ) {

        foreach( array_column( $DB->query( '
            SELECT  code
            FROM    ' . DB_PREFIX . $type . '
        ' )->fetch_all( MYSQLI_ASSOC ), 'code' ) as $region ) {

            sitemap_entry( 'airports/' . $type . '/' . $region, 0.7, 'weekly' );

        }

    }

    /* types + restrictions */

    foreach( [ 'type', 'restriction' ] as $type ) {

        foreach( array_column( $DB->query( '
            SELECT   ' . $type . ' AS col
            FROM     ' . DB_PREFIX . 'airport
            WHERE    ' . $type . ' IS NOT NULL
            GROUP BY ' . $type . '
        ' )->fetch_all( MYSQLI_ASSOC ), 'col' ) as $col ) {

            sitemap_entry( 'airports/' . $type . '/' . $col, 0.7, 'weekly' );

        }

    }

    /* timezones */

    foreach( array_column( $DB->query( '
        SELECT  ident
        FROM    ' . DB_PREFIX . 'timezone
    ' )->fetch_all( MYSQLI_ASSOC ), 'ident' ) as $tz ) {

        sitemap_entry( 'airports/timezone/' . $tz, 0.7, 'weekly' );

    }

    /* build index */

    $index = [];

    foreach( array_chunk( $sitemap, 25000 ) as $i => $chunk ) {

        $index[] = '<sitemap>
            <loc>' . SITE . 'sitemap-' . $i . '.xml</loc>
        </sitemap>';

        file_put_contents( BASE . 'sitemap-' . $i . '.xml', '<?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ' .
            'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" ' .
            'xmlns:mobile="http://www.google.com/schemas/sitemap-mobile/1.0" ' .
            'xmlns:xhtml="http://www.w3.org/1999/xhtml">
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