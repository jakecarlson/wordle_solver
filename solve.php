<?php
require_once('config.php');
require_once('functions.php');

define('WORDS_LIST', array_map('trim', file(inject_source(WORDS_SCORED_FILE))));
define('RANDOM_WORD', WORDS_LIST[array_rand(WORDS_LIST)]);
define('TOP_GUESSES', array_map('trim', file(inject_source(WORDS_GUESSES_FILE))));

/* ==== */

if (count($argv) > 2) {
    $arg = $argv[2];
} else {
    $arg = ',,|,,|,,|,,|,,';
    line("USE TEMPLATE: e,n,l|e,n,l|e,n,i|e,n,l|e,n,l; start with {$arg}");
    line("Where [e] = exact matches, [n] = nonmatches, and [l] = loose matches");
}
$board = parse_board($arg);

$turn = 6;
if (count($argv) > 3) {
    $turn = intval($argv[3]);
}

$guess = get_next_guess($board, TOP_GUESSES, WORDS_LIST, $turn, ADAPTIVE_GUESS_THRESHOLD, POSSIBILITIES_THRESHOLD);

if (!$guess) {
    result("UNSOLVABLE");
} else {
    result("NEXT GUESS: {$guess}");
}