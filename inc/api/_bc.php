<?php

    require_once __DIR__ . '/../apm.php';

    echo $DB->query( '
        SELECT  name
        FROM    ' . DB_PREFIX . [
            'T' => 'continent',
            'C' => 'country',
            'R' => 'region'
        ][ $_POST['type'] ?? 'T' ] . '
        WHERE   code = "' . strtoupper( $_POST['ident'] ?? '' ) . '"
    ' )->fetch_all()[0][0] ?? 'Unknown';

?>