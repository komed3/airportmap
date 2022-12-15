<?php

    require_once __DIR__ . '/inc/config.php';
    require_once __DIR__ . '/inc/classes/APM.php';

    $APM = new APM();
    $APM->run();

?>