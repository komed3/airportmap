<?php

    require_once __DIR__ . '/api.php';

    $dir = FILES . 'images/';

    if( ( $res = $DB->query( '
        SELECT  _id, url
        FROM    ' . DB_PREFIX . 'image
        WHERE   file IS NULL
        LIMIT   0, 100
    ' ) )->num_rows > 0 ) {

        while( $row = $res->fetch_object() ) {

            $uuid = uniqid();
            $fext = pathinfo( $row->url )['extension'];

            $blob = api_url2file( $row->url );

            $file = new Imagick();
            $file->readImageBlob( $blob );

            //

            $file->destroy();

            exit;

        }

    }

    //api_exit( [] );

?>