<?php

    function temp_in(
        float $temp,
        string $in = 'c'
    ) {

        return round( $temp, 1 ) . '&#8239;' . i18n( 'temp-' . $in );

    }

    function wind_info(
        array $weather
    ) {

        return i18n( 'wind-' . ( ( $weather['wind_spd'] ?? 0 ) == 0 ? 'calm' : 'to' ),
            round( $weather['wind_spd'] ?? 0 ),
            __cardinal( $weather['wind_dir'] ?? 0 )
        );

    }

    function vis_info(
        array $weather
    ) {

        $horiz = round( ( $weather['vis_horiz'] ?? 10 ) * 1.609, 1 );
        $vert = round( $weather['vis_vert'] ?? 99999 );

        return '<span>' . ( $horiz >= 10 ? '10km+' : ( $horiz < 1
            ? round( $horiz * 1000 ) . '&#8239;m'
            : $horiz . '&#8239;km'
        ) ) . '</span><span>/</span><span>' . ( $vert > 50000
            ? i18n( 'clear-sky' )
            : $vert . '&#8239;ft'
        ) . '</span>';

    }

    function wx(
        array $weather
    ) {

        $text = [];

        if( empty( $weather['wx'] ) ) {

            return i18n( 'cloud-' . ( $weather['cloud_1_cover'] ?? 'CLR' ) );

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

        $wx = $weather['wx'] ?? ( $weather['cloud_1_cover'] ?? 'CLR' );

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
            'SKC' => 'sunny',
            'CLR' => 'sunny',
            'CAVOK' => 'sunny',
            'FEW' => 'partly_cloudy_day',
            'SCT' => 'partly_cloudy_day',
            'BKN' => 'cloudy',
            'OVC' => 'cloudy',
            'OVX' => 'cloudy'
        ] as $probe => $icon ) {

            if( stripos( $wx, $probe ) !== false ) {

                return $icon;

            }

        }

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

?>