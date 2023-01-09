<?php

    function temp_in(
        float $temp,
        string $in = 'c',
        int $digits = 0
    ) {

        return '<x>' . __number( $temp, $digits ) . '</x>&#8239;' . i18n( 'temp-' . $in );

    }

    function altim_in(
        float $altim,
        string $in = 'inhg',
        int $digits = 2
    ) {

        return '<x>' . __number( $altim, $digits ) . '</x>&#8239;' . i18n( 'altim-' . $in );

    }

    function wind_in(
        float $wind,
        string $in = 'kt'
    ) {

        return '<x>' . __number( $wind ) . '</x>&#8239;' . i18n( 'wind-' . $in );

    }

    function wind_info(
        array $weather,
        bool $gust = false
    ) {

        return i18n(
            'wind-' . (
                ( $weather['wind_spd'] ?? 0 ) == 0 ? 'calm' : 'to'
            ) . (
                $gust && $weather['wind_gust'] ? '-gust' : ''
            ),
            __number( $weather['wind_spd'] ?? 0 ),
            __cardinal( $weather['wind_dir'] ?? 0 ),
            __number( $weather['wind_gust'] ?? 0 )
        );

    }

    function vis_info(
        array $weather,
        bool $labels = false
    ) {

        $horiz = round( ( $weather['vis_horiz'] ?? 10 ) * 1.609, 1 );
        $vert = round( $weather['vis_vert'] ?? 99999 );

        if( $labels ) {

            return '<span class="label">' . i18n( 'weather-visibility' ) . '</span><b>' . ( $horiz >= 10
                ? ucfirst( i18n( 'vis-10' ) )
                : ( $horiz < 1
                    ? __number( $horiz * 1000 ) . '&#8239;m'
                    : __number( $horiz ) . '&#8239;km'
                )
            ) . '</b>—<span class="label">' . i18n( 'weather-ceiling' ) . '</span><b>' . ( $vert > 20000
                ? ucfirst( i18n( 'ceiling-none' ) )
                : __number( $vert ) . '&#8239;ft'
            ) . '</b>';

        }

        return '<span>' . ( $horiz >= 10 ? '10&#8239;km+' : ( $horiz < 1
            ? __number( $horiz * 1000 ) . '&#8239;m'
            : __number( $horiz ) . '&#8239;km'
        ) ) . '</span><span>/</span><span>' . ( $vert > 20000
            ? i18n( 'clear-sky' )
            : __number( $vert ) . '&#8239;ft'
        ) . '</span>';

    }

    function wx(
        array $weather
    ) {

        $text = [];

        if( empty( $weather['wx'] ) || in_array( $weather['wx'], [
            'CAVOK', 'CLR', 'FEW', 'SCT', 'BKN', 'OVC'
        ] ) ) {

            return i18n( 'cloud-' . ( $weather['wx'] ?? 'CLR' ) );

        }

        foreach( array_unique( explode( ' ', $weather['wx'] ) ) as $symbol ) {

            $raw = preg_match( '/^(\+|-|VC|RE)?([A-Z]{2})([A-Z]{2})?$/i', $symbol, $matches );

            $parts = array_filter( array_map( function( $p ) {
                return i18n_save( 'wx-' . $p );
            }, $matches ) );

            if( !empty( array_intersect( $matches, [ 'TS', 'SH' ] ) ) ) {

                array_pop( $parts );

            }

            $text[] = trim( implode( ' ', array_unique( $parts ) ) );

        }

        return trim( implode( ', ', array_unique( $text ) ) );

    }

    function wx_icon(
        array $weather
    ) {

        $night = ( ( $h = date( 'G', $weather['gmt_offset'] * 60 + time() ) ) <= 6 ) || ( $h >= 19 );

        $wx = $weather['wx'] ?? 'CLR';

        foreach( [
            'FZ' => 'ac_unit',
            'SH' => 'rainy',
            'TS' => 'thunderstorm',
            'BR' => 'waves',
            'DS' => 'waves',
            'DU' => 'waves',
            'DZ' => 'rainy',
            'FC' => 'tornado',
            'FG' => 'foggy',
            'FU' => 'dehaze',
            'GR' => 'grain',
            'GS' => 'grain',
            'HZ' => 'dehaze',
            'IC' => 'ac_unit',
            'PE' => 'ac_unit',
            'PO' => 'storm',
            'PY' => 'rainy',
            'RA' => 'rainy',
            'SA' => 'storm',
            'SG' => 'grain',
            'SN' => 'weather_snowy',
            'SQ' => 'tsunami',
            'SS' => 'storm',
            'VA' => 'blur_on',
            'SKC' => $night ? 'clear_night' : 'clear_day',
            'CLR' => $night ? 'clear_night' : 'clear_day',
            'CAVOK' => $night ? 'clear_night' : 'clear_day',
            'FEW' => $night ? 'partly_cloudy_night' : 'partly_cloudy_day',
            'SCT' => $night ? 'partly_cloudy_night' : 'partly_cloudy_day',
            'BKN' => 'cloudy',
            'OVC' => 'cloudy',
            'OVX' => 'cloudy'
        ] as $probe => $icon ) {

            if( stripos( $wx, $probe ) !== false ) {

                return $icon;

            }

        }

    }

    function relhum(
        array $weather
    ) {

        return 100 * (
            exp( ( 17.625 * $weather['dewp'] ) / ( 243.04 + $weather['dewp'] ) ) /
            exp( ( 17.625 * $weather['temp'] ) / ( 243.04 + $weather['temp'] ) )
        );

    }

    function airdens(
        array $weather
    ) {

        return ( $weather['altim'] * 3386.3886 ) / ( 287.0500676 * ( $weather['temp'] + 273.15 ) );

    }

    function windchill(
        array $weather
    ) {

        $v = pow( $weather['wind_spd'] * 1.852, 0.16 );

        return 13.12 + ( 0.6215 * $weather['temp'] ) - ( 11.37 * $v ) + ( 0.3965 * $weather['temp'] * $v );

    }

    function sky_chart(
        array $weather
    ) {

        $layer = $legend = $label = [];

        for( $i = 1; $i <= 4; $i++ ) {

            if( !empty( $cover = $weather[ 'cloud_' . $i . '_cover' ] ) &&
                is_numeric( $base = $weather[ 'cloud_' . $i . '_base' ] ?? 0 ) ) {

                $layer[ $base ] = strtoupper( trim( $cover ) );

            }

        }

        $max = ceil( max( 1000, ...array_keys( $layer ) ) / 1000 ) * 1000;

        for( $base = 0; $base <= $max; $base += ceil( $max / 2500 ) * 500 ) {

            $legend[] = '<div class="label" style="bottom: ' . ( $base / $max * 100 ) . '%;">
                <span>' . alt_in( $base, 'ft' ) . '</span>
            </div>';

        }

        foreach( $layer as $base => $cover ) {

            if( in_array( $cover, [ 'CLR', 'SKC', 'CLEAR', 'CAVOK' ] ) ) {

                $layer[ $base ] = '<div class="layer msg">
                    <span>' . $cover . '</span>
                </div>';

            } else {

                $bottom = $base / $max * 100;

                $layer[ $base ] = '<div class="layer layer-' . $cover . '" style="bottom: ' . $bottom . '%;">
                    ' . str_repeat( '<div class="cloud"></div>', 18 ) . '
                </div>';

                $label[] = '<div class="label layer-' . $cover . '" style="bottom: ' . $bottom . '%;">
                    <span>' . i18n( 'skychart-label', $cover, alt_in( $base, 'ft' ) ) . '</span>
                </div>';

            }

        }

        return '<div class="skychart">
            <div class="legend">
                ' . implode( '', $legend ) . '
            </div>
            <div class="layers">
                ' . implode( '', $layer ) . '
            </div>
            <div class="labels">
                ' . implode( '', $label ) . '
            </div>
        </div>';

    }

    function sigmet_hazard(
        array $sigmet
    ) {

        return ucfirst( implode( ' ', array_filter( [
            i18n_save( 'qualifier-' . $sigmet['qualifier'] ),
            i18n_save( 'hazard-' . $sigmet['hazard'] )
        ] ) ) );

    }

    function sigmet_valid(
        array $sigmet
    ) {

        $valid_from = strtotime( $sigmet['valid_from'] );
        $valid_to = strtotime( $sigmet['valid_to'] );

        return i18n( date( 'd', $valid_from ) != date( 'd', $valid_to )
                ? 'valid-until' : 'valid-from-to',
            date( 'm/d', $valid_from ),
            date( 'H:i', $valid_from ),
            date( 'm/d', $valid_to ),
            date( 'H:i', $valid_to )
        );

    }

    function sigmet_move(
        array $sigmet
    ) {

        return ( $sigmet['dir'] ? i18n( 'movement',
            i18n( 'dir-' . $sigmet['dir'] ),
            (int) $sigmet['spd'] . '&#8239;kt'
        ) : i18n( 'stationary' ) );

    }

    function sigmet_cng(
        array $sigmet
    ) {

        return ucfirst( i18n( 'change-' . ( $sigmet['cng'] ?? 'NC' ) ) );

    }

    function sigmet_fl(
        array $sigmet
    ) {

        $fl_msgkey = 'fl';

        if( !empty( $fl_base = $sigmet['low_1'] ?? ( $sigmet['low_2'] ?? null ) ) )
            $fl_msgkey .= '-from';

        if( !empty( $fl_top = $sigmet['hi_1'] ?? ( $sigmet['hi_2'] ?? null ) ) )
            $fl_msgkey .= '-to';

        return $fl_base || $fl_top ? ucfirst( i18n( $fl_msgkey,
            str_pad( (int) $fl_base / 100, 3, '0', STR_PAD_LEFT ),
            str_pad( (int) $fl_top / 100, 3, '0', STR_PAD_LEFT )
        ) ) : null;

    }

    function sigmet_info(
        array $sigmet,
        bool $location = true
    ) {

        return '<li>
            <i class="icon">schedule</i>
            <span>' . sigmet_valid( $sigmet ) . '</span>
        </li>
        <li>
            <i class="icon">near_me</i>
            <span>' . sigmet_move( $sigmet ) . '</span>
        </li>
        <li>
            <i class="icon">warning</i>
            <span>' . sigmet_cng( $sigmet ) . '</span>
        </li>
        ' . ( empty( $fl = sigmet_fl( $sigmet ) ) ? '' : '<li>
            <i class="icon">flight_takeoff</i>
            <span>' . $fl . '</span>
        </li>' ) . '
        ' . ( !$location || empty( $airport = airport_by( 'ICAO', $sigmet['airport'] ) ) ? '' : '<li>
            <i class="icon">location_on</i>
            <span>' . airport_link( $airport ) . '</span>
        </li>' );

    }

    function flight_cat_count() {

        global $DB;

        $cats = [];

        foreach( $DB->query( '
            SELECT   m.flight_cat AS cat,
                     COUNT( a.ICAO ) AS cnt
            FROM     ' . DB_PREFIX . 'metar m,
                     ' . DB_PREFIX . 'airport a
            WHERE    a.ICAO = m.station
            AND      reported >= DATE_SUB( NOW(), INTERVAL 1 DAY )
            GROUP BY cat
        ' )->fetch_all( MYSQLI_ASSOC ) as $cat ) {

            $cats[ $cat['cat'] ?? 'UNK' ] = $cat['cnt'];

        }

        return $cats;

    }

    function station_list(
        array $stations,
        int $page = 1,
        string $baseurl = ''
    ) {

        if( count( $stations ) == 0 ) {

            $content = '<div class="empty">
                <i class="icon">flight_takeoff</i>
                <span class="label">' . i18n( 'search-results-empty' ) . '</span>
            </div>';

        } else {
        
            $pagination = $page == -1 ? '' : pagination( count( $stations ), $page, $baseurl );

            $content = $pagination . '<div class="list">';

            foreach( array_slice( $stations, ( $page - 1 ) * 24, 24 ) as $station ) {

                $content .= '<div class="row station cat-' . ( $station['flight_cat'] ?? 'UNK' ) . '">
                    <div class="cat">
                        <span>' . i18n( 'cat-' . ( $station['flight_cat'] ?? 'UNK' ) ) . '</span>
                    </div>
                    <div class="info">
                        <div class="headline">
                            <a href="' . SITE . 'airport/' . $station['ICAO'] . '" class="name">' . $station['name'] . '</a>
                        </div>
                        <div class="weather">
                            <div class="wx">
                                <i class="icon">' . wx_icon( $station ) . '</i>
                                <span>' . ucfirst( wx( $station ) ) . '</span>
                            </div>
                            <div class="temp">
                                <i class="icon">device_thermostat</i>
                                <span>' . temp_in( $station['temp'], 'c' ) . '</span>
                            </div>
                            <div class="wind">
                                <i class="icon">air</i>
                                <span>' . wind_info( $station, true ) . '</span>
                            </div>
                            <div class="vis">
                                <i class="icon">routine</i>
                                ' . vis_info( $station ) . '
                            </div>
                        </div>
                    </div>
                </div>';

            }

            $content .= '</div>' . $pagination;

        }

        return '<div class="stationlist">
            ' . $content . '
        </div>';

    }

    function _station_list(
        array $stations,
        int $page = 1,
        string $baseurl = ''
    ) {

        echo station_list( $stations, $page, $baseurl );

    }

?>