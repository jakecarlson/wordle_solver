<?php
const DEFAULT_WORDS_SOURCE = 'wordle';
const WORDS_SOURCE_FILE = './[source].words.txt';
const WORDS_SCORED_FILE = './[source].scored.txt';
const WORDS_GUESSES_FILE = './[source].guesses.txt';
const WORDS_RAW_FILE = './[source].words.html';
const ADAPTIVE_GUESS_THRESHOLD = 3;
const POSSIBILITIES_THRESHOLD = 5;
const NUM_LETTERS = 5;
const NUM_TURNS = 6;
const DEBUG = false;

if (count($argv) > 1) {
    define('WORDS_SOURCE', $argv[1]);
} else {
    define('WORDS_SOURCE', DEFAULT_WORDS_SOURCE);
}