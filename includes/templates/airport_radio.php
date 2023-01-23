<?php

    global $DB, $airport;

    if( empty( $airport ) ) {

        __404();

    }

?>
<div class="airport-radio content-normal">
    <h2 class="secondary-headline"><?php _i18n( 'airport-frequencies' ); ?></h2>
    <?php if( count( $radios = $DB->query( '
        SELECT  *
        FROM    ' . DB_PREFIX . 'frequency
        WHERE   airport = "' . $airport['ICAO'] . '"
    ' )->fetch_all( MYSQLI_ASSOC ) ) > 0 ) {

        _radio_list( $airport, $radios );

    } else { ?>
        <p><?php _i18n( 'airport-frequencies-empty' ); ?></p>
    <?php } if( count( $navaids = $DB->query( '
        SELECT  *
        FROM    ' . DB_PREFIX . 'navaid
        WHERE   airport = "' . $airport['ICAO'] . '"
    ' )->fetch_all( MYSQLI_ASSOC ) ) > 0 ) { ?>
        <h2 class="secondary-headline"><?php _i18n( 'airport-navaids' ); ?></h2>
        <?php _navaid_list( $airport, $navaids ); ?>
    <?php } if( count( $waypoints = $DB->query( '
        SELECT   *, ( 3440.29182 * acos(
            cos( radians( ' . $airport['lat'] . ' ) ) *
            cos( radians( lat ) ) *
            cos(
                radians( lon ) -
                radians( ' . $airport['lon'] . ' )
            ) +
            sin( radians( ' . $airport['lat'] . ' ) ) *
            sin( radians( lat ) )
        ) ) AS distance
        FROM     ' . DB_PREFIX . 'waypoint
        WHERE    ( lat BETWEEN ' . ( $airport['lat'] - 1 ) . ' AND ' . ( $airport['lat'] + 1 ) . ' )
        AND      ( lon BETWEEN ' . ( $airport['lon'] - 1 ) . ' AND ' . ( $airport['lon'] + 1 ) . ' )
        ORDER BY distance ASC
        LIMIT    0, 48
    ' )->fetch_all( MYSQLI_ASSOC ) ) > 0 ) { ?>
        <h2 class="secondary-headline"><?php _i18n( 'airport-waypoints' ); ?></h2>
        <?php _waypoint_list( $waypoints ); ?>
    <?php } ?>
</div>