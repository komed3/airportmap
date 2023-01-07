<?php

    require_once __DIR__ . '/api.php';

    $sitemap = [];

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

        $sitemap[] = '<url>
            <loc>' . SITE . $site . '</loc>
            <changefreq>daily</changefreq>
            <priority>' . $prior . '</priority>
        </url>';

    }

    /* airports */

    foreach( array_column( $DB->query( '
        SELECT   ICAO
        FROM     ' . DB_PREFIX . 'airport
        ORDER BY tier DESC
    ' )->fetch_all( MYSQLI_ASSOC ), 'ICAO' ) as $airport ) {

        $sitemap[] = '<url>
            <loc>' . SITE . 'airport/' . $airport . '/info</loc>
            <changefreq>monthly</changefreq>
            <priority>0.9</priority>
        </url>';

        $sitemap[] = '<url>
            <loc>' . SITE . 'airport/' . $airport . '/weather</loc>
            <changefreq>hourly</changefreq>
            <priority>0.9</priority>
        </url>';

        $sitemap[] = '<url>
            <loc>' . SITE . 'airport/' . $airport . '/nearby</loc>
            <changefreq>monthly</changefreq>
            <priority>0.8</priority>
        </url>';

        $sitemap[] = '<url>
            <loc>' . SITE . 'airport/' . $airport . '/radio</loc>
            <changefreq>monthly</changefreq>
            <priority>0.7</priority>
        </url>';

        $sitemap[] = '<url>
            <loc>' . SITE . 'airport/' . $airport . '/runways</loc>
            <changefreq>monthly</changefreq>
            <priority>0.7</priority>
        </url>';

    }

    /* regions */

    foreach( [ 'continent', 'country', 'region' ] as $type ) {

        foreach( array_column( $DB->query( '
            SELECT  code
            FROM    ' . DB_PREFIX . $type . '
        ' )->fetch_all( MYSQLI_ASSOC ), 'code' ) as $region ) {

            $sitemap[] = '<url>
                <loc>' . SITE . 'airports/' . $type . '/' . $region . '</loc>
                <changefreq>monthly</changefreq>
                <priority>0.7</priority>
            </url>';

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

            $sitemap[] = '<url>
                <loc>' . SITE . 'airports/' . $type . '/' . $col . '</loc>
                <changefreq>monthly</changefreq>
                <priority>0.7</priority>
            </url>';

        }

    }

    /* timezones */

    foreach( array_column( $DB->query( '
        SELECT  ident
        FROM    ' . DB_PREFIX . 'timezone
    ' )->fetch_all( MYSQLI_ASSOC ), 'ident' ) as $tz ) {

        $sitemap[] = '<url>
            <loc>' . SITE . 'airports/timezone/' . $tz . '</loc>
            <changefreq>monthly</changefreq>
            <priority>0.7</priority>
        </url>';

    }

    /* build index */

    $index = [];

    foreach( array_chunk( $sitemap, 25000 ) as $i => $chunk ) {

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