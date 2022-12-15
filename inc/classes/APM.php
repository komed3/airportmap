<?php

    class APM {

        public function __construct() {

            require_once __DIR__ . '/Coord.php';
            require_once __DIR__ . '/DB.php';

            require_once __DIR__ . '/Airport.php';

            $a = new Airport( 'EDCJ' );

            echo $a->coord( 'dms' );

        }

        public function run() {



        }

    }

?>