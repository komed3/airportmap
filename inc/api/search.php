<?php

    require_once __DIR__ . '/../apm.php';

    $searchtext = $_POST['searchtext'] ?? null;

    $results = airport_search( $searchtext );

    if( count( $results ) == 1 && strpos(
        parse_url( $_POST['referrer'] )['path'],
        '/search/'
    ) === false ) {

        echo json_encode( [
            'redirect_to' => 'airport/' . $results[0]['ICAO']
        ] );

        exit;

    }

    $content = airport_search_form() . ( !empty( $searchtext ) ? '
    <h1 class="primary-headline">
        <i class="icon">near_me</i>
        <span class="label" data-i18n="Search results for"></span>
        <b>' . $searchtext . '</b>
    </h1>
    ' . airport_list( $results ) : '' );

    echo json_encode( [
        'title' => 'Search results',
        'page' => 'airports',
        'content' => $content,
        'searchtext' => $searchtext
    ] );

?>