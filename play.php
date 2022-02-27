<?php
require_once('config.php');
require_once('functions.php');

define('WORDS_LIST', array_map('trim', file(WORDS_SCORED_FILE)));
define('RANDOM_WORD', WORDS_LIST[array_rand(WORDS_LIST)]);
define('TOP_GUESSES', array_map('trim', file(WORDS_GUESSES_FILE)));

/* ==== */

$num_guesses = [];
$num_fails = 0;
foreach (WORDS_LIST as $word_to_guess) {

    // Set up board data structure
    $board = [
        'matches'       => [],
        'nonmatches'    => [],
        'includes'      => [],
    ];
    foreach (range(1, NUM_LETTERS) as $pos) {
        $board['matches'][$pos] = false;
        $board['nonmatches'][$pos] = [];
        $board['includes'][$pos] = [];
    }

    echo "\nWORD: " . $word_to_guess . "\n\n";

    foreach (range(0,NUM_TURNS-1) as $i) {

        $turn = $i+1;

        $guess = get_next_guess($turn, $board, TOP_GUESSES, WORDS_LIST);

        if (!$guess) {
            echo "ERROR: no more possibilities?\n";
            break;
        }

        $board = make_guess($guess, $board, $word_to_guess);

        if ($board) {
            $line = "Turn {$turn}: {$guess}: ";
            foreach (range(1,NUM_LETTERS) as $pos) {
                $nonmatches = implode('', $board['nonmatches'][$pos]);
                $includes = implode('', $board['includes'][$pos]);
                $line .= "{$board['matches'][$pos]},{$nonmatches},{$includes}|";
            }
            $line = substr($line, 0, -1);
            echo "{$line}\n";
        } else {
            $num_guesses[] = $turn;
            break;
        }

    }

    echo "\n================================\n";
    if (!$board) {
        echo "SUCCESS: {$guess} ({$turn} turns)\n";
    } else {
        echo "FAIL: could not determine word.\n";
        ++$num_fails;
    }
    echo "================================\n\n";

}

$num_guesses = array_filter($num_guesses);
$average_guesses = array_sum($num_guesses) / count($num_guesses);
$num_words = count(WORDS_LIST);
$perc_fails = number_format(($num_fails / $num_words) * 100, 2);
echo "================================\n";
echo "AVERAGE GUESSES: {$average_guesses}\n";
echo "PERCENT FAILS: {$perc_fails}% ({$num_fails} / {$num_words})\n";
echo "================================\n\n";