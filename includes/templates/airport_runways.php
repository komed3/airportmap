<?php

    global $DB, $airport;

    if( empty( $airport ) ) {

        __404();

    }

?>
<div class="airport-runways content-normal">
    <?php if( count( $runways = $DB->query( '
        SELECT  *
        FROM    ' . DB_PREFIX . 'runway
        WHERE   airport = "' . $airport['ICAO'] . '"
    ' )->fetch_all( MYSQLI_ASSOC ) ) > 0 ) {

        _runway_list( $airport, $runways );

    } else { ?>
        <p><?php _i18n( 'airport-runways-empty' ); ?></p>
    <?php } ?>
</div>