<?php

    require_once __DIR__ . '/../apm.php';

    $searchtext = $_POST['searchtext'] ?? null;

    $results = airport_search( $searchtext );

    $content = airport_search_form() . ( !empty( $searchtext ) ? '
    <h1 class="primary-headline">
        <i class="icon">near_me</i>
        <span class="label" data-i18n="Search results for"></span>
        <b>' . $searchtext . '</b>
    </h1>' : '' );

    echo json_encode( [
        'title' => 'Search results',
        'page' => 'airports',
        'content' => $content,
        'searchtext' => $searchtext
    ] );

?>