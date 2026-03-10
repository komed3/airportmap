<?php

    if( ( $ICAO = $DB->query( '
        SELECT  *
        FROM    ' . DB_PREFIX . 'ICAO
        WHERE   code = "' . ( $path[2] ?? '' ) . '"
    ' ) )->num_rows == 0 ) __404();

    $ICAO = $ICAO->fetch_object();
    $plain = str_replace( '*', '', $ICAO->code );

    $countries = $regions = [];

    foreach( explode( '|', $ICAO->regions ) as $r ) {
        ${ strlen( $r ) == 2 ? 'countries' : 'regions' }[] = $r;
    }

    $regions_query = ' AND ( country IN ( "' .
        implode( '", "', $countries ) . '" ) OR region IN ( "' .
        implode( '", "', $regions ) . '" ) ) ';

    $airports = $DB->query( '
        SELECT   *
        FROM     ' . DB_PREFIX . 'airport
        WHERE    ICAO LIKE "' . $plain . '%"
        ' . $regions_query . '
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
        ' . $regions_query . '
    ' )->fetch_object();

    $count = 0;
    $links = [];

    if( strlen( $ICAO->code ) == 1 ) {

        $rows = $DB->query( '
            SELECT   *
            FROM     ICAO
            WHERE    code LIKE "' . $plain . '%"
            AND      code != "' . $plain . '"
        ' )->fetch_all( MYSQLI_ASSOC );

        foreach( $rows as $row ) {

            $c = $r = [];

            foreach( explode( '|', $row['regions'] ) as $reg ) {
                ${ strlen( $reg ) == 2 ? 'c' : 'r' }[] = $reg;
            }

            $cnt = $DB->query( '
                SELECT   ICAO
                FROM     ' . DB_PREFIX . 'airport a
                WHERE    ICAO LIKE "' . str_replace( '*', '', $row['code'] ) . '%"
                AND    ( country IN ( "' .
                    implode( '", "', $c ) . '" ) OR region IN ( "' .
                    implode( '", "', $r ) . '" )
                )
            ' )->num_rows;

            $links[] = [
                'page' => $row['code'],
                'name' => $row['name'],
                'cnt' => $cnt
            ];

            $count += $cnt;

        }

    } else {

        $count = count( $airports );

    }

    $breadcrumbs = [ [ 'world' ] ];

    for( $i = 1; $i <= strlen( $ICAO->code ); $i++ ) {
        $breadcrumbs[] = [ 'ICAO', substr( $ICAO->code, 0, $i ) ];
    }

    $__site_canonical = 'airports/ICAO/' . $ICAO->code;

    $__site_title = i18n( 'airports-icao-title', $ICAO->code, $ICAO->name );
    $__site_desc = i18n( 'airports-icao-desc', $ICAO->code, $ICAO->name );

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
            'ICAO' => $plain,
            '__' => $regions_query
        ],
        'fit_bounds' => [
            [ $position->lat_min, $position->lon_min ],
            [ $position->lat_max, $position->lon_max ]
        ]
    ], 'minimal-ui' ); ?>
    <h1 class="primary-headline">
        <i class="icon">language</i>
        <span><?php echo $__site_title; ?></span>
		<b><?php echo __number( $count ); ?></b>
    </h1>
    <?php _breadcrumbs( $breadcrumbs ); ?>
    <?php if( count( $links ) > 0 ) _pagelist( 'airports/ICAO', $links ); ?>
    <div class="content-normal">
        <?php _airport_list(
            $airports, $path[3] ?? 1,
            'airports/ICAO/' . $ICAO->code
        ); ?>
    </div>
</div>
<?php _footer(); ?>
