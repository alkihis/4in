<?php

define('MAIN_DIR', $_SERVER['DOCUMENT_ROOT'] . '/');

require MAIN_DIR . 'inc/cst.php';
require MAIN_DIR . 'inc/func.php';

// Fichier principal

$page = explode('/api/', $_SERVER['REQUEST_URI'])[1];

$page_without_query_string = explode('?', $page)[0];

$final_page = str_replace('.json', '.php', $page_without_query_string);

if (file_exists(MAIN_DIR . 'api/' . $final_page)) {
    require MAIN_DIR . 'api/' . $final_page;
}
else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
}
