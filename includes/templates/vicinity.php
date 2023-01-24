<?php

    $lat = (float) ( $path[1] ?? 40.7 );
    $_lat = __DMS( $lat, 'lat' );

    $lon = (float) ( $path[2] ?? -74 );
    $_lon = __DMS( $lon, 'lon' );

    $base = 'vicinity/' . $lat . '/' . $lon;

    $__site_canonical = base_url( 'vicinity' );

    $__site_title = i18n( 'vicinity-title-at', $_lat, $_lon );
    $__site_desc = i18n( 'vicinity-desc' );

    add_resource( 'vicinity', 'css', 'vicinity.css' );

    _header();

?>
<div class="content-full vicinity">
    <?php _map( [
        'type' => 'airport',
        'navaids' => true,
        'waypoints' => true,
        'supress_sigmets' => true,
        'supress_day_night' => true,
        'position' => [
            'lat' => $lat,
            'lon' => $lon,
            'zoom' => 10
        ],
        'marker' => [ [
            'lat' => $lat,
            'lon' => $lon
        ] ]
    ], 'minimal-ui' ); ?>
    <form class="vicinityform" data-form="vicinity" autocomplete="off">
        <span class="label"><?php _i18n( 'vicinity-label' ); ?></span>
        <input class="coord" type="number" name="lat" min="-90" max="90" step="0.00001" placeholder="<?php _i18n( 'latitude' ); ?>" value="<?php
            echo number_format( $lat, 5 ); ?>" />
        <input class="coord" type="number" name="lon" min="-180" max="180" step="0.00001" placeholder="<?php _i18n( 'longitude' ); ?>" value="<?php
            echo number_format( $lon, 5 ); ?>" />
        <button type="submit" name="vicinitysubmit" title="<?php _i18n( 'vicinity-submit-title' ); ?>">
            <i class="icon">near_me</i>
            <span><?php _i18n( 'vicinity-submit' ); ?></span>
        </button>
        <button class="my" data-action="vicinity-my" name="vicinitymy" title="<?php _i18n( 'vicinity-my-title' ); ?>">
            <i class="icon">my_location</i>
            <span><?php _i18n( 'vicinity-my' ); ?></span>
        </button>
    </form>
    <h1 class="primary-headline">
        <i class="icon">share_location</i>
        <span><?php _i18n( 'vicinity-title' ); ?></span>
        <b><?php echo $_lat; ?></b>
        <b><?php echo $_lon; ?></b>
    </h1>
    <div class="content-normal">
        <?php _airport_list(
            airport_nearest( $lat, $lon, [], 5 ),
            $path[3] ?? 1,
            $base,
            [ $lat, $lon ]
        ); ?>
        <h2><?php _i18n( 'vicinity-navaids' ); ?></h2>
        <?php if( count( $navaids = $DB->query( '
            SELECT   *, ( 3440.29182 * acos(
                cos( radians( ' . $lat . ' ) ) *
                cos( radians( lat ) ) *
                cos(
                    radians( lon ) -
                    radians( ' . $lon . ' )
                ) +
                sin( radians( ' . $lat . ' ) ) *
                sin( radians( lat ) )
            ) ) AS distance
            FROM     ' . DB_PREFIX . 'navaid
            WHERE    ( lat BETWEEN ' . ( $lat - 1 ) . ' AND ' . ( $lat + 1 ) . ' )
            AND      ( lon BETWEEN ' . ( $lon - 1 ) . ' AND ' . ( $lon + 1 ) . ' )
            ORDER BY distance ASC
            LIMIT    0, 48
        ' )->fetch_all( MYSQLI_ASSOC ) ) > 0 ) { ?>
            <?php _navaid_list( [ 'lat' => $lat, 'lon' => $lon ], $navaids ); ?>
        <?php } else { ?>
            <p><?php _i18n( 'vicinity-navaids-empty' ); ?></p>
        <?php } ?>
        <h2><?php _i18n( 'vicinity-waypoints' ); ?></h2>
        <?php if( count( $waypoints = $DB->query( '
            SELECT   *, ( 3440.29182 * acos(
                cos( radians( ' . $lat . ' ) ) *
                cos( radians( lat ) ) *
                cos(
                    radians( lon ) -
                    radians( ' . $lon . ' )
                ) +
                sin( radians( ' . $lat . ' ) ) *
                sin( radians( lat ) )
            ) ) AS distance
            FROM     ' . DB_PREFIX . 'waypoint
            WHERE    ( lat BETWEEN ' . ( $lat - 1 ) . ' AND ' . ( $lat + 1 ) . ' )
            AND      ( lon BETWEEN ' . ( $lon - 1 ) . ' AND ' . ( $lon + 1 ) . ' )
            ORDER BY distance ASC
            LIMIT    0, 48
        ' )->fetch_all( MYSQLI_ASSOC ) ) > 0 ) { ?>
            <?php _waypoint_list( [ 'lat' => $lat, 'lon' => $lon ], $waypoints ); ?>
        <?php } else { ?>
            <p><?php _i18n( 'vicinity-waypoints-empty' ); ?></p>
        <?php } ?>
    </div>
</div>
<?php _footer(); ?>