<?php

    class APM {

        protected static $DB;

        public function __construct() {

            require_once __DIR__ . '/DB.php';

            $this->DB = new DB(
                DB_HOST, DB_USER, DB_PASSWORD,
                DB_NAME, DB_PORT
            );

            $this->DB->set_charset( DB_CHARSET );

        }

        public function run() {



        }

    }

?>