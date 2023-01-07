<?php

    if( ( $type = $DB->query( '
        SELECT  type
        FROM    ' . DB_PREFIX . 'airport
        WHERE   type = "' . ( $path[2] ?? '' ) . '"
        LIMIT   0, 1
    ' ) )->num_rows != 1 ) {

        __404();

    }

    $type = $type->fetch_object()->type;
    $name = i18n_save( 'airport-typep-' . $type ) ?? i18n( 'unknown' );

    $airports = $DB->query( '
        SELECT   *
        FROM     ' . DB_PREFIX . 'airport
        WHERE    type = "' . $type . '"
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
        WHERE   type = "' . $type . '"
    ' )->fetch_object();

    $count = count( $airports );
    $_count = __number( $count );

    $__site_canonical = $base . 'airports/type/' . $type;

    $__site_title = i18n( 'airports-type-title', $name, $type, $_count );
    $__site_desc = i18n( 'airports-type-desc', $name, $type, $_count );

    add_resource( 'region', 'css', 'region.css' );

    _header();

?>
<div class="region">
    <?php _map( [
        'type' => 'airport',
        'navaids' => false,
        'supress_sigmets' => true,
        'supress_day_night' => true,
        'query' => [
            'types' => [ $type ]
        ],
        'fit_bounds' => [
            [ $position->lat_min, $position->lon_min ],
            [ $position->lat_max, $position->lon_max ]
        ]
    ], 'minimal-ui' ); ?>
    <h1 class="primary-headline">
        <i class="icon">flight_takeoff</i>
        <span><?php echo $name; ?></span>
        <b><?php echo $_count; ?></b>
    </h1>
    <div class="content-normal">
        <?php _back_to( 'airports', i18n( 'airports-title' ) ); ?>
        <?php _airport_list(
            $airports, $path[3] ?? 1,
            'airports/type/' . $type
        ); ?>
    </div>
</div>
<?php _footer(); ?>