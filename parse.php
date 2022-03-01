<?php
require_once('config.php');
require_once('functions.php');

$html = file_get_contents(inject_source(WORDS_RAW_FILE));
$html_lined = str_replace('</li>', "</li>\n", $html);
$stripped = strip_tags($html_lined);
$alpha = preg_replace('/[0-9]+/', '', $stripped);
file_put_contents(inject_source(WORDS_SOURCE_FILE), $alpha);
line("Parsed word list to " . inject_source(WORDS_SOURCE_FILE) . ".");
br(2);
