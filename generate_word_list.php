<?php
$html = file_get_contents(WORDS_RAW_FILE);
$html_lined = str_replace('</li>', "</li>\n", $html);
$stripped = strip_tags($html_lined);
$alpha = preg_replace('/[0-9]+/', '', $stripped);
file_put_contents(WORDS_SOURCE_FILE, $alpha);
