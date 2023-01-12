<?php

    require_once __DIR__ . '/api.php';

    $dir = FILES . 'images/';
    $count = 0;

    if( ( $res = $DB->query( '
        SELECT  _id, url
        FROM    ' . DB_PREFIX . 'image
        WHERE   file IS NULL
        LIMIT   0, 25
    ' ) )->num_rows > 0 ) {

        while( $row = $res->fetch_object() ) {

            /* fetch image */

            $uuid = uniqid();
            $fext = str_replace( 'jpg', 'jpeg', strtolower( pathinfo( $row->url )['extension'] ) );
            $name = $uuid . '.' . $fext;

            $blob = api_url2file( $row->url );

            /* create image */

            $file = new Imagick();
            $file->readImageBlob( $blob );
            $file->stripImage();

            /* image width + height */

            $width = $file->getImageWidth();
            $height = $file->getImageHeight();

            if( $width > 2560 ) {

                $height = round( 2560 / $width * $height );
                $width = 2560;

            }

            /* resize image */

            $file->thumbnailImage( $width, $height );

            /* compress image */

            $file->setImageCompressionQuality( 85 );

            /* set image as based its own type */

            switch( $fext ) {

                case 'jpg':

                    $file->setImageFormat( 'jpeg' );
                    $file->setSamplingFactors( [ '2x2', '1x1', '1x1'] );

                    $profiles = $file->getImageProfiles( 'icc', true );

                    $file->stripImage();

                    if( !empty( $profiles ) ) {

                        $file->profileImage( 'icc', $profiles['icc'] );

                    }

                    $file->setInterlaceScheme( Imagick::INTERLACE_JPEG );
                    $file->setColorspace( Imagick::COLORSPACE_SRGB );

                    break;

                case 'png':

                    $imagick->setImageFormat( 'png' );

                    break;

                case 'gif':

                    $imagick->setImageFormat( 'gif' );

                    break;

                default:
                    break;

            }

            /* write image */

            $file->writeImage( $dir . $name );

            /* create thumbnail image */

            $file->adaptiveResizeImage( 720, 540, true );
            $file->setImageCompressionQuality( 75 );

            /* write thumbnail */

            $file->writeImage( $dir . 'thumb-' . $name );

            /* destroy image */

            $file->destroy();

            /* save file in database */

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