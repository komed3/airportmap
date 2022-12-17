<?php

    require_once __DIR__ . '/../apm.php';

    $searchtext = $_POST['searchtext'];

    $results = airport_search( $searchtext );

    $content = '';

    echo json_encode( [
        'title' => 'Search results',
        'page' => 'airports',
        'content' => $content,
        'searchtext' => $searchtext
    ] );

?>