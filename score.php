<?php
require_once('config.php');
require_once('functions.php');

define('WORDS_LIST', array_map('trim', file(inject_source(WORDS_SOURCE_FILE))));

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

head("TOP GUESSES BY SCORE & UNIQUENESS");
foreach ($top_words as $word=>$score) {
    line("{$word}: {$score}");
}

// Output top individual letter positions
head("TOP 10 LETTERS BY POSITION");
foreach ($positions as $pos=>$arr) {
    line("POSITION {$pos}:", true);
    $i = 0;
    foreach ($arr as $letter=>$score) {
        if ($i == 10) {
            break;
        }
        line("{$letter}: {$score}");
        ++$i;
    }
    br();
}

// Output top letters
head("TOP LETTERS");
foreach ($letters as $letter=>$score) {
    line("{$letter}: {$score}");
}

// Output top words
head("TOP WORDS");
foreach ($scores as $word=>$score) {
    line("{$word}: {$score}");
}
br();

file_put_contents(inject_source(WORDS_SCORED_FILE), implode("\n", array_keys($scores)));
line("Output scored words file to " . inject_source(WORDS_SCORED_FILE));
file_put_contents(inject_source(WORDS_GUESSES_FILE), implode("\n", array_keys($top_words)));
line("Output top guesses file to " . inject_source(WORDS_GUESSES_FILE));
br(2);