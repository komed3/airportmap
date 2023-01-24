<?php

    if( ( $timezone = $DB->query( '
        SELECT  *
        FROM    ' . DB_PREFIX . 'timezone
        WHERE   ident = "' . ( $path[2] ?? '' ) . '"
    ' ) )->num_rows == 0 ) {

        __404();

    }

    $timezone = $timezone->fetch_object();
    $tz_offset = tz_offset( $timezone->gmt_offset );

    $airports = $DB->query( '
        SELECT   *
        FROM     ' . DB_PREFIX . 'airport
        WHERE    timezone = "' . $timezone->short . '"
        AND      gmt_offset = ' . $timezone->gmt_offset . '
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
        WHERE   timezone = "' . $timezone->short . '"
        AND     gmt_offset = ' . $timezone->gmt_offset . '
    ' )->fetch_object();

    $count = count( $airports );
    $_count = __number( $count );

    $__site_canonical = $base . 'airports/timezone/' . $timezone->ident;

    $__site_title = i18n( 'airports-timezone-title', $timezone->name, $timezone->short, $_count, $tz_offset );
    $__site_desc = i18n( 'airports-timezone-desc', $timezone->name, $timezone->short, $_count, $tz_offset );

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
            'timezone' => $timezone->short,
            'gmt_offset' => $timezone->gmt_offset
        ],
        'fit_bounds' => [
            [ $position->lat_min, $position->lon_min ],
            [ $position->lat_max, $position->lon_max ]
        ]
    ], 'minimal-ui' ); ?>
    <h1 class="primary-headline">
        <i class="icon">schedule</i>
        <span><?php echo $timezone->name; ?></span>
        <span><?php echo $tz_offset; ?></span>
        <b><?php echo $_count; ?></b>
    </h1>
    <div class="content-normal">
        <?php _back_to( 'airports', i18n( 'airports-title' ) ); ?>
        <?php _airport_list(
            $airports, $path[3] ?? 1,
            'airports/timezone/' . $timezone->ident
        ); ?>
    </div>
</div>
<?php _footer(); ?>