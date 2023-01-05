<?php

    if( ( $continent = $DB->query( '
        SELECT  *
        FROM    ' . DB_PREFIX . 'continent
        WHERE   code = "' . ( $path[2] ?? '' ) . '"
    ' ) )->num_rows == 0 ) {

        __404();

    }

    $continent = $continent->fetch_object();

    $list = $DB->query( '
        SELECT   c.code AS code,
                 c.name AS name,
                 COUNT( a.ICAO ) AS cnt
        FROM     ' . DB_PREFIX . 'country c,
                 ' . DB_PREFIX . 'airport a
        WHERE    c.continent = "' . $continent->code . '"
        AND      a.country = c.code
        GROUP BY c.code
        ORDER BY c.code ASC
    ' )->fetch_all( MYSQLI_ASSOC );

    $position = $DB->query( '
        SELECT  MIN( lat ) AS lat_min,
                MIN( lon ) AS lon_min,
                MAX( lat ) AS lat_max,
                MAX( lon ) AS lon_max,
                AVG( lat ) AS lat_avg,
                AVG( lon ) AS lon_avg
        FROM    ' . DB_PREFIX . 'airport
        WHERE   continent = "' . $continent->code . '"
    ' )->fetch_object();

    $count = array_sum( array_column( $list, 'cnt' ) );
    $_count = __number( $count );

    $__site_canonical = $base . 'airports/continent/' . $continent->code;

    $__site_title = i18n( 'airports-continent-title', $continent->name, $continent->code, $_count );
    $__site_desc = i18n( 'airports-continent-desc', $continent->name, $continent->code, $_count );

    add_resource( 'region', 'css', 'region.css' );

    _header();

?>
<div class="region">
    <?php _map( [
        'type' => 'airport',
        'navaids' => false,
        'supress_sigmets' => false,
        'supress_day_night' => false,
        'query' => [
            'continent' => $continent->code
        ],
        'fit_bounds' => [
            [ $position->lat_min, $position->lon_min ],
            [ $position->lat_max, $position->lon_max ]
        ]
    ], 'minimal-ui' ); ?>
    <h1 class="primary-headline">
        <i class="icon">language</i>
        <span><?php echo $continent->name; ?></span>
        <b><?php echo $_count; ?></b>
    </h1>
    <?php _breadcrumbs( [
        [ 'world' ],
        [ 'continent', $continent->code ]
    ] ); ?>
    <?php _region_list( 'country', $list ); ?>
</div>
<?php _footer(); ?>