<?php

    require_once __DIR__ . '/api.php';

    $dir = FILES . 'images/';
    $count = 0;

    if( ( $res = $DB->query( '
        SELECT  _id, url
        FROM    ' . DB_PREFIX . 'image
        WHERE   file IS NULL
        LIMIT   0, 50
    ' ) )->num_rows > 0 ) {

        while( $row = $res->fetch_object() ) {

            $uuid = uniqid();
            $fext = pathinfo( $row->url )['extension'];
            $name = $uuid . '.' . $fext;

            $blob = api_url2file( $row->url );

            $file = new Imagick();
            $file->readImageBlob( $blob );

            $file->setImageCompression( Imagick::COMPRESSION_JPEG );
            $file->setImageCompressionQuality( 95 );

            $file->writeImage( $dir . $name );

            $file->adaptiveResizeImage( 720, 540, true );
            $file->setImageCompressionQuality( 75 );

            $file->writeImage( $dir . 'thumb-' . $name );

            $file->destroy();

            if( $DB->query( '
                UPDATE  ' . DB_PREFIX . 'image
                SET     file = "' . $name . '"
                WHERE   _id = ' . $row->_id
            ) ) {

                $count++;

            }

        }

    }

    api_exit( [
        'images' => $count
    ] );

?>