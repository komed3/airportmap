<?php

    class Coord {

        private static $lat;

        private static $lon;

        public function __construct(
            float $lat,
            float $lon
        ) {

            $this->lat = $lat;
            $this->lon = $lon;

        }

        public function toDMS() {

            $lat = $lon = '';

            foreach( [
                'lat' => $this->lat,
                'lon' => $this->lon
            ] as $c => $d ) {

                $a = abs( $d );
                $g = floor( $a );
                $t = ( $a - $g ) * 60;
                $m = floor( $t );
                $s = floor( ( $t - $m ) * 60 );

                $$c = sprintf(
                    '%d°&#8239;%d′&#8239;%d″&#8239;%s',
                    $g, $m, $s, [
                        'lat' => $d < 0 ? 'S' : 'N',
                        'lon' => $d < 0 ? 'W' : 'E'
                    ][ $c ]
                );

            }

            return $lat . ', ' . $lon;

        }

    }

?>