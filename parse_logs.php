<?php
$log = file_get_contents('storage/logs/laravel.log');
preg_match_all('/\[\d{4}-\d{2}-\d{2}.*?\] local\.ERROR:.*?Stack trace:/s', $log, $matches);
if (!empty($matches[0])) {
    echo substr(end($matches[0]), 0, 2000);
} else {
    echo 'No error found';
}
