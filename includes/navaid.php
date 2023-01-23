<?php

    function navaid_ident(
        array $navaid
    ) {

        return '<morse>' . __morse( $navaid['ident'] ) . '</morse>';

    }

    function navaid_level(
        array $navaid
    ) {

        return i18n( 'level-' . $navaid['level'] );

    }

    function navaid_power(
        array $navaid
    ) {

        return i18n( 'power-' . $navaid['power'] );

    }

    function navaid_list(
        array $airport,
        array $navaids
    ) {

        $list = '';

        foreach( $navaids as $navaid ) {

            $list .= '<div class="navaid navaid-' . $navaid['type'] . '">
                <navicon></navicon>
                <div class="info">
                    <div class="headline">
                        <span class="ident">' . $navaid['ident'] . '</span>
                        <morse>' . __morse( $navaid['ident'] ) . '</morse>
                    </div>
                    <div class="freq">' . format_freq( $navaid['frequency'] ) . '</div>
                    <div class="line">
                        <span class="type">' . $navaid['type'] . '</span>
                        <span class="name">' . $navaid['name'] . '</span>
                    </div>
                    ' . ( empty( $navaid['level'] ) ? '' : '<div class="line">
                        <span class="usage">' . i18n( 'level-' . $navaid['level'] ) . '</span>
                        <span class="power">(' . i18n( 'power-' . $navaid['power'] ) . ')</span>
                    </div>' ) . '
                    <div class="line">
                        ' . __DMS_coords( $navaid['lat'], $navaid['lon'] ) . '
                    </div>
                    <div class="line">
                        <span>' . alt_in( $navaid['alt'], 'ft' ) . '</span>
                        <span>(' . alt_in( $navaid['alt'] / 3.281, 'm&nbsp;MSL' ) . ')</span>
                    </div>
                </div>
            </div>';

        }

        return '<div class="navaidlist">
            ' . $list . '
        </div>';

    }

    function _navaid_list(
        array $airport,
        array $navaids
    ) {

        echo navaid_list( $airport, $navaids );

    }

    function waypoint_list(
        array $airport,
        array $waypoints
    ) {

        $list = '';

        foreach( $waypoints as $waypoint ) {

            $list .= '<div class="waypoint">
                <wpicon></wpicon>
                <div class="info">
                    <div class="headline">
                        <span class="ident">' . $waypoint['ident'] . '</span>
                        <morse>' . __morse( $waypoint['ident'] ) . '</morse>
                    </div>
                    <div class="line coords">
                        ' . __DMS_coords( $waypoint['lat'], $waypoint['lon'] ) . '
                    </div>
                    <div class="line dist">
                        <i class="icon">near_me</i>
                        <span>' . alt_in( $waypoint['distance'], 'nm' ) . '</span>
                        <span>' . __cardinal( __HDG(
                            $airport['lat'], $airport['lon'],
                            $waypoint['lat'], $waypoint['lon']
                        ) ) . '</span>
                    </div>
                </div>
            </div>';

        }

        return '<div class="waypointlist">
            ' . $list . '
        </div>';

    }

    function _waypoint_list(
        array $airport,
        array $waypoints
    ) {

        echo waypoint_list( $airport, $waypoints );

    }

?>