<?php

    if( ( $ICAO = $DB->query( '
        SELECT  *
        FROM    ' . DB_PREFIX . 'ICAO
        WHERE   code = "' . ( $path[2] ?? '' ) . '"
    ' ) )->num_rows == 0 ) {

        __404();

    }

    $ICAO = $ICAO->fetch_object();
    $plain = str_replace( '*', '', $ICAO->code );

    $airports = $DB->query( '
        SELECT   *
        FROM     ' . DB_PREFIX . 'airport
        WHERE    ICAO LIKE "' . $plain . '%"
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
        WHERE   ICAO LIKE "' . $plain . '%"
        AND     LENGTH( ICAO ) = 4
    ' )->fetch_object();

    $count = count( $airports );
    $_count = __number( $count );

    $breadcrumbs = [ [ 'world' ] ];

    for( $i = 1; $i <= strlen( $ICAO->code ); $i++ ) {

        $breadcrumbs[] = [ 'ICAO', substr( $ICAO->code, 0, $i ) ];

    }

    $__site_canonical = $base . 'airports/ICAO/' . $ICAO->code;

    $__site_title = i18n( 'airports-icao-title', $ICAO->code, $ICAO->name, $_count );
    $__site_desc = i18n( 'airports-icao-desc', $ICAO->code, $ICAO->name, $_count );

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
            'ICAO' => $plain
        ],
        'fit_bounds' => [
            [ $position->lat_min, $position->lon_min ],
            [ $position->lat_max, $position->lon_max ]
        ]
    ], 'minimal-ui' ); ?>
    <h1 class="primary-headline">
        <i class="icon">language</i>
        <span><?php echo $__site_title; ?></span>
        <b><?php echo $_count; ?></b>
    </h1>
    <?php _breadcrumbs( $breadcrumbs ); ?>
    <?php if( strlen( $ICAO->code ) == 1 ) {

        _pagelist( 'airports/ICAO', $DB->query( '
            SELECT   i.code AS page,
                     CONCAT( i.code, ": ", i.name ) AS name,
                     COUNT( a.ICAO ) AS cnt
            FROM     ' . DB_PREFIX . 'ICAO i,
                     ' . DB_PREFIX . 'airport a
            WHERE    i.code LIKE "' . $ICAO->code . '%"
            AND      i.code != "' . $ICAO->code . '"
            AND      a.ICAO LIKE CONCAT( i.code, "%" )
            GROUP BY i.code
            ORDER BY i.code ASC
        ' )->fetch_all( MYSQLI_ASSOC ) );

    } ?>
    <div class="content-normal">
        <?php _airport_list(
            $airports, $path[3] ?? 1,
            'airports/ICAO/' . $ICAO->code
        ); ?>
    </div>
</div>
<?php _footer(); ?>