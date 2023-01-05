<?php

    require_once __DIR__ . '/api.php';

    $sitemap = [];

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