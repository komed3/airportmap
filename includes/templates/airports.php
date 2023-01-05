<?php

    $list = $DB->query( '
        SELECT   c.code AS page,
                 c.name AS name,
                 COUNT( a.ICAO ) AS cnt
        FROM     ' . DB_PREFIX . 'continent c,
                 ' . DB_PREFIX . 'airport a
        WHERE    a.continent = c.code
        GROUP BY c.code
        ORDER BY c.code ASC
    ' )->fetch_all( MYSQLI_ASSOC );

    $count = array_sum( array_column( $list, 'cnt' ) );
    $_count = __number( $count );

    $__site_canonical = $base . 'airports';

    $__site_title = i18n( 'airports-title', $_count );
    $__site_desc = i18n( 'airports-desc', $_count );

    add_resource( 'region', 'css', 'region.css' );

    _header();

?>
<div class="region">
    <?php _map( [
        'type' => 'airport',
        'navaids' => false,
        'supress_sigmets' => false,
        'supress_day_night' => false,
        'fit_bounds' => [
            [ 90, -180 ],
            [ -90, 180 ]
        ]
    ], 'minimal-ui' ); ?>
    <h1 class="primary-headline">
        <i class="icon">language</i>
        <span><?php _i18n( 'region-world' ); ?></span>
        <b><?php echo $_count; ?></b>
    </h1>
    <h2 class="secondary-headline content-normal"><?php _i18n( 'airports-by-region' ); ?></h2>
    <?php _pagelist( 'airports/continent', $list ); ?>
    <h2 class="secondary-headline content-normal"><?php _i18n( 'airports-by-type' ); ?></h2>
    <h2 class="secondary-headline content-normal"><?php _i18n( 'airports-by-restriction' ); ?></h2>
    <h2 class="secondary-headline content-normal"><?php _i18n( 'airports-by-zone' ); ?></h2>
</div>
<?php _footer(); ?>