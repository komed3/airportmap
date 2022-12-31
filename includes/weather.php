<?php

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