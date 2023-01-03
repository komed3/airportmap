<?php

    require_once __DIR__ . '/api.php';

    $sitemap = [];

    /* Airports */

    foreach( $DB->query( '
        SELECT   ICAO
        FROM     ' . DB_PREFIX . 'airport
        ORDER BY tier DESC
    ' )->fetch_all( MYSQLI_ASSOC ) as $airport ) {

        $sitemap[] = '<url>
            <loc>' . SITE . 'airport/' . $airport['ICAO'] . '/info</loc>
            <changefreq>monthly</changefreq>
            <priority>0.9</priority>
        </url>';

        $sitemap[] = '<url>
            <loc>' . SITE . 'airport/' . $airport['ICAO'] . '/weather</loc>
            <changefreq>hourly</changefreq>
            <priority>0.9</priority>
        </url>';

        $sitemap[] = '<url>
            <loc>' . SITE . 'airport/' . $airport['ICAO'] . '/nearby</loc>
            <changefreq>monthly</changefreq>
            <priority>0.8</priority>
        </url>';

        $sitemap[] = '<url>
            <loc>' . SITE . 'airport/' . $airport['ICAO'] . '/radio</loc>
            <changefreq>monthly</changefreq>
            <priority>0.7</priority>
        </url>';

        $sitemap[] = '<url>
            <loc>' . SITE . 'airport/' . $airport['ICAO'] . '/runways</loc>
            <changefreq>monthly</changefreq>
            <priority>0.7</priority>
        </url>';

    }

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

    file_put_contents( BASE . 'sitemap-index.xml', '<?xml version="1.0" encoding="UTF-8"?>
    <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
        ' . implode( '', $index ) . '
    </sitemapindex>' );

?>