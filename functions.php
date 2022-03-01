<?php
require_once('./config.php');

// Output a line
function line($str, $breakBefore = false) {
    if ($breakBefore) {
        br();
    }
    echo $str . br();
}

// Output line break
function br($num = 1) {
    echo str_repeat("\n", $num);
}

// Output a header
function head($str, $extraBreak = false) {
    line($str, true);
    hr();
    if ($extraBreak) {
        br();
    }
}

// Output horizontal rule
function hr() {
    line(str_repeat('-', 32));
}

// Output a result
function result($str) {
    line(str_repeat('=', 32), true);
    line($str);
    line(str_repeat('=', 32));
    br(2);
}

// Show to STDOUT if DEBUG flag is true
function show($str) {
    if (DEBUG) {
        echo $str;
    }
}

// Count the number of duplicate characters in a string
function num_duplicate_chars($str): int {
    $arr = str_split($str);
    return count($arr) - count(array_flip($arr));
}

// Get the top scored words given a duplicate tolerance
function get_top_words($words, $tolerance = 0, $used_letters = []): array {
    $result = [
        'words'         => [],
        'used_letters'  => $used_letters,
    ];
    foreach ($words as $word=>$score) {
        if (num_duplicate_chars($word) <= $tolerance) {
            $word_letters = str_split($word);
            $dupes = 0;
            foreach ($word_letters as $letter) {
                if (in_array($letter, $result['used_letters'])) {
                    ++$dupes;
                }
            }
            if ($dupes <= $tolerance) {
                $result['words'][$word] = $score;
                $result['used_letters'] = array_merge($result['used_letters'], $word_letters);
            }
        }
    }
    return $result;
}

// Parse the board
function parse_board($str = '') {

    // Initialize types
    $board = [
        'matches'       => [],
        'nonmatches'    => [],
        'includes'      => [],
    ];

    // If an existing board was passed in, fill'er up
    if (!empty($str)) {
        foreach (explode('|', $str) as $i=>$types) {
            $pos = $i+1;
            $parts = explode(',', $types);
            $board['matches'][$pos] = $parts[0];
            $board['nonmatches'][$pos] = $parts[1];
            $board['includes'][$pos] = $parts[2];
        }

    // Otherwise just initialize empty array for each position
    } else {
        foreach (range(1, NUM_LETTERS) as $pos) {
            $board['matches'][$pos] = false;
            $board['nonmatches'][$pos] = [];
            $board['includes'][$pos] = [];
        }
    }

    return $board;

}

// Make the guess and return the modified assumption data
function make_guess($guess, $board, $word_to_guess): bool|array {

    if ($guess == $word_to_guess) {
        return false;
    }

    $guess_chars = str_split($guess);
    $word_chars = str_split($word_to_guess);

    foreach ($guess_chars as $i=>$char) {
        $pos = $i+1;
        if ($char == $word_chars[$i]) {
            $board['matches'][$pos] = $char;
        } else if (str_contains($word_to_guess, $char)) {
            $board['includes'][$pos][] = $char;
        } else {
            $board['nonmatches'][$pos][] = $char;
        }
    }

    return $board;

}

// Get the next best guess given what's already been guessed and the list of possibilities
function get_next_guess($turn, $board, $guesses, $words): string {

    if ($turn < ADAPTIVE_GUESS_THRESHOLD) {
        return $guesses[$turn-1];
    }

    $possibilities = [];
    foreach ($words as $word) {

        show("\n");
        show(strtoupper($word) . "\n----------------\n");

        $chars = str_split($word);
        $is_match = true;

        show("match tests:\n");
        foreach ($board['matches'] as $pos=>$letter) {
            $i = $pos-1;
            if (!empty($letter)) {
                show("{$pos}. {$chars[$i]} == {$letter}: ");
                if ($chars[$i] != $letter) {
                    show("failed!\n");
                    $is_match = false;
                    break 1;
                }
                show("ok\n");
            }
        }

        if ($is_match) {
            show("\nnon-match tests:\n");
            foreach ($board['nonmatches'] as $pos=>$letters) {
                $i = $pos-1;
                show("{$pos}.\n");
                foreach ($letters as $letter) {
                    show("  {$chars[$i]} != {$letter}: ");
                    if (in_array($letter, $board['matches']) || in_array($letter, $board['includes'])) {
                        if ($chars[$i] == $letter) {
                            show("failed!\n");
                            $is_match = false;
                            break 2;
                        }
                    } else {
                        if (str_contains($word, $letter)) {
                            show("failed\n");
                            $is_match = false;
                            break 2;
                        }
                    }
                    show("ok\n");
                }
            }
        }

        if ($is_match) {
            show("\ninclude tests:\n");
            foreach ($board['includes'] as $pos=>$letters) {
                $i = $pos-1;
                if (!empty($letters)) {
                    show("{$pos}.\n");
                    foreach ($letters as $letter) {
                        if (!empty($letter)) {
                            show("  contains {$letter} AND {$chars[$i]} != {$letter}: ");
                            if (!str_contains($word, $letter) || ($chars[$i] == $letter)) {
                                show("failed!\n");
                                $is_match = false;
                                break 2;
                            }
                            show("ok\n");
                        }
                    }
                }
            }
        }

        if ($is_match) {
            $possibilities[] = $word;
        }

    }

    $num_possibilities = count($possibilities);
    $perc_possibilities = number_format(($num_possibilities / count(WORDS_LIST)) * 100, 2);
    show("\n{$num_possibilities} remaining possibilities ({$perc_possibilities}%)\n");

    if ($perc_possibilities > POSSIBILITIES_THRESHOLD) {
        return $guesses[$turn-1];
    } else {
        return $possibilities[0];
    }

}