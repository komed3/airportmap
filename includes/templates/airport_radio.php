<?php

    global $DB, $airport;

    if( empty( $airport ) ) {

        __404();

    }

?>
<div class="airport-radio content-normal">
    <h2 class="secondary-headline"><?php _i18n( 'airport-frequencies' ); ?></h2>
    <?php if( count( $radios = $DB->query( '
        SELECT  *
        FROM    ' . DB_PREFIX . 'frequency
        WHERE   airport = "' . $airport['ICAO'] . '"
    ' )->fetch_all( MYSQLI_ASSOC ) ) > 0 ) {

        _radio_list( $airport, $radios );

    } else { ?>
        <p><?php _i18n( 'airport-frequencies-empty' ); ?></p>
    <?php } if( count( $navaids = $DB->query( '
        SELECT  *
        FROM    ' . DB_PREFIX . 'navaid
        WHERE   airport = "' . $airport['ICAO'] . '"
    ' )->fetch_all( MYSQLI_ASSOC ) ) > 0 ) { ?>
        <h2 class="secondary-headline"><?php _i18n( 'airport-navaids' ); ?></h2>
        <?php _navaid_list( $airport, $navaids ); ?>
    <?php } ?>
</div>