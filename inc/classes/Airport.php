<?php

    class Airport {

        private static $DB;

        private static $data;

        public function __construct(
            string $search = ''
        ) {

            $this->DB = new DB();

            if( !empty( $search ) && ( $res = $this->find( $search ) ) ) {

                $this->data = $res->fetch_object();

            }

        }

        public function find(
            string $search
        ) {

            if( $this->DB ) {

                return $this->DB->query( '
                    SELECT  *
                    FROM    ' . DB_PREFIX . 'airport
                    WHERE   ICAO = "' . $search . '"
                    OR      IATA = "' . $search . '"
                    OR      GPS = "' . $search . '"
                    OR      LOCAL = "' . $search . '"
                    OR      name = "' . $search . '"
                ' );

            }

        }

        public function getData(
            string $key
        ) {

            return !empty( $this->data ) && property_exists( $this->data, $key )
                ? $this->data->{ $key } : null;

        }

        public function ICAO() {

            return $this->getData( 'ICAO' );

        }

        public function name() {

            return $this->getData( 'name' );

        }

        public function coord(
            string $format = 'decimal'
        ) {

            $lat = $this->getData( 'lat' );
            $lon = $this->getData( 'lon' );

            switch( strtolower( trim( $format ) ) ) {

                case 'decimal':
                    return $lat . ', ' . $lon;

                case 'dms':
                    return ( new Coord( $lat, $lon ) )->toDMS();

                default:
                    return '';

            }

        }

    }

?>