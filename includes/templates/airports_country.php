<?php

    if( ( $country = $DB->query( '
        SELECT  *
        FROM    ' . DB_PREFIX . 'country
        WHERE   code = "' . ( $path[2] ?? '' ) . '"
    ' ) )->num_rows == 0 ) {

        __404();

    }

    $country = $country->fetch_object();

    $list = $DB->query( '
        SELECT   r.code AS page,
                 r.name AS name,
                 COUNT( a.ICAO ) AS cnt
        FROM     ' . DB_PREFIX . 'region r,
                 ' . DB_PREFIX . 'airport a
        WHERE    r.country = "' . $country->code . '"
        AND      a.region = r.code
        GROUP BY r.code
        ORDER BY r.code ASC
    ' )->fetch_all( MYSQLI_ASSOC );

    $position = $DB->query( '
        SELECT  MIN( lat ) AS lat_min,
                MIN( lon ) AS lon_min,
                MAX( lat ) AS lat_max,
                MAX( lon ) AS lon_max,
                AVG( lat ) AS lat_avg,
                AVG( lon ) AS lon_avg
        FROM    ' . DB_PREFIX . 'airport
        WHERE   country = "' . $country->code . '"
    ' )->fetch_object();

    $count = array_sum( array_column( $list, 'cnt' ) );
    $_count = __number( $count );

    $__site_canonical = 'airports/country/' . $country->code;

    $__site_title = i18n( 'airports-country-title', $country->name ?? i18n( 'unknown' ), $country->code, $_count );
    $__site_desc = i18n( 'airports-country-desc', $country->name ?? i18n( 'unknown' ), $country->code, $_count );

    add_resource( 'region', 'css', 'region.min.css' );

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
            'country' => $country->code
        ],
        'fit_bounds' => [
            [ $position->lat_min, $position->lon_min ],
            [ $position->lat_max, $position->lon_max ]
        ]
    ], 'minimal-ui' ); ?>
    <h1 class="primary-headline">
        <i class="icon">language</i>
        <span><?php echo ( $country->name ?? i18n( 'unknown' ) ); ?></span>
        <b><?php echo $_count; ?></b>
    </h1>
    <?php _breadcrumbs( [
        [ 'world' ],
        [ 'continent', $country->continent ],
        [ 'country', $country->code ]
    ] ); ?>
    <?php _pagelist( 'airports/region', $list ); ?>
    <div class="content-normal">
        <?php _airport_list(
            $DB->query( '
                SELECT   *
                FROM     ' . DB_PREFIX . 'airport
                WHERE    country = "' . $country->code . '"
                ORDER BY tier DESC
            ' )->fetch_all( MYSQLI_ASSOC ),
            $path[3] ?? 1,
            'airports/country/' . $country->code
        ); ?>
    </div>
</div>
<?php _footer(); ?>