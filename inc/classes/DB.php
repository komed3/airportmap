<?php

    class DB extends Mysqli {

        public function __construct() {

            parent::__construct(
                DB_HOST,
                DB_USER,
                DB_PASSWORD,
                DB_NAME,
                DB_PORT
            );

            parent::set_charset(
                DB_CHARSET
            );

        }

    }

?>