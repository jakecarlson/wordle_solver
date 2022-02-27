<?php
require_once('config.php');
require_once('functions.php');

define('WORDS_LIST', array_map('trim', file(WORDS_SOURCE_FILE)));

// Set up letters
$letters = [];
foreach (range('a', 'z') as $letter) {
    $letters[$letter] = 0;
}

// Set up positions
$positions = [];
foreach (range(1,NUM_LETTERS) as $pos) {
    $positions[$pos] = [];
    foreach (range('a', 'z') as $letter) {
        $positions[$pos][$letter] = 0;
    }
}

// Loop through words
foreach (WORDS_LIST as $word) {
    $word_letters = str_split($word);
    foreach (range(1,NUM_LETTERS) as $pos) {
        $letter = $word_letters[$pos-1];
        ++$letters[$letter];
        ++$positions[$pos][$letter];
    }
}

// Sort letters and positions
arsort($letters);
foreach ($positions as $pos=>$arr) {
    arsort($positions[$pos]);
}

// Score the words
$scores = [];
foreach (WORDS_LIST as $word) {
    $scores[$word] = 0;
    $parts = str_split($word);
    foreach ($parts as $i=>$letter) {
        $pos = $i+1;
        if (isset($positions[$pos][$letter])) {
            $scores[$word] += $positions[$pos][$letter];
        }
    }
}
arsort($scores);

$used_letters = [];
$top_words = [];
foreach (range(0,3) as $tolerance) {
    $result = get_top_words($scores, $tolerance, $used_letters);
    $top_words = array_merge($top_words, $result['words']);
    $used_letters = array_merge($used_letters, $result['used_letters']);
}

echo "\nTOP GUESSES BY SCORE & UNIQUENESS:\n";
foreach ($top_words as $word=>$score) {
    echo "{$word}: {$score}\n";
}
echo "\n";

// Output top individual letter positions
echo "TOP 10 LETTERS BY POSITION:\n\n";
foreach ($positions as $pos=>$arr) {
    echo "POSITION {$pos}:\n";
    $i = 0;
    foreach ($arr as $letter=>$score) {
        if ($i == 10) {
            break;
        }
        echo "{$letter}: {$score}\n";
        ++$i;
    }
    echo "\n";
}

// Output top letters
echo "TOP LETTERS:\n";
foreach ($letters as $letter=>$score) {
    echo "{$letter}: {$score}\n";
}
echo "\n";

// Output top words
echo "TOP WORDS:\n";
foreach ($scores as $word=>$score) {
    echo "{$word}: {$score}\n";
}
echo "\n";

file_put_contents(WORDS_SCORED_FILE, implode("\n", array_keys($scores)));
file_put_contents(WORDS_GUESSES_FILE, implode("\n", array_keys($top_words)));