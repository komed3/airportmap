<?php

    if( ( $region = $DB->query( '
        SELECT  *
        FROM    ' . DB_PREFIX . 'region
        WHERE   code = "' . ( $path[2] ?? '' ) . '"
    ' ) )->num_rows == 0 ) {

        __404();

    }

    $region = $region->fetch_object();

    $airports = $DB->query( '
        SELECT   *
        FROM     ' . DB_PREFIX . 'airport
        WHERE    region = "' . $region->code . '"
        ORDER BY tier DESC
    ' )->fetch_all( MYSQLI_ASSOC );

    $position = $DB->query( '
        SELECT  MIN( lat ) AS lat_min,
                MIN( lon ) AS lon_min,
                MAX( lat ) AS lat_max,
                MAX( lon ) AS lon_max,
                AVG( lat ) AS lat_avg,
                AVG( lon ) AS lon_avg
        FROM    ' . DB_PREFIX . 'airport
        WHERE   region = "' . $region->code . '"
    ' )->fetch_object();

    $count = count( $airports );
    $_count = __number( $count );

    $country = region_name( 'country', $region->country );

    $__site_canonical = 'airports/region/' . $region->code;

    $__site_title = i18n( 'airports-region-title', $region->name ?? i18n( 'unknown' ), $region->code, $_count, $country );
    $__site_desc = i18n( 'airports-region-desc', $region->name ?? i18n( 'unknown' ), $region->code, $_count, $country );

    add_resource( 'region', 'css', 'region.css' );

    _header();

?>
<div class="region">
    <?php _map( [
        'type' => 'airport',
        'navaids' => false,
        'waypoints' => false,
        'supress_sigmets' => true,
        'supress_day_night' => true,
        'query' => [
            'region' => $region->code
        ],
        'fit_bounds' => [
            [ $position->lat_min, $position->lon_min ],
            [ $position->lat_max, $position->lon_max ]
        ]
    ], 'minimal-ui' ); ?>
    <h1 class="primary-headline">
        <i class="icon">language</i>
        <span><?php echo $country; ?></span>
        <span>/</span>
        <span><?php echo ( $region->name ?? i18n( 'unknown' ) ); ?></span>
        <b><?php echo $_count; ?></b>
    </h1>
    <?php _breadcrumbs( [
        [ 'world' ],
        [ 'continent', $region->continent ],
        [ 'country', $region->country ],
        [ 'region', $region->code ]
    ] ); ?>
    <div class="content-normal">
        <?php _airport_list(
            $airports, $path[3] ?? 1,
            'airports/region/' . $region->code
        ); ?>
    </div>
</div>
<?php _footer(); ?>