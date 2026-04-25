<?php

namespace App\Lib;

use NlpTools\Tokenizers\WhitespaceAndPunctuationTokenizer;

class SearchHelper
{
    public static function stripPunctuation($string): string
    {
        $string = strtolower($string);
        $string = preg_replace(   '/[[:punct:]]/'   ," ", $string);
        $string = preg_replace('/\+\s*\+/', '+', $string);
        $string = preg_replace("[!\"\#\$%&'\(\)*,.\/\:;\<=\>\?@\\\^_‘\{\|\}~]", " ", $string);
        return trim($string);
    }

    public static function convertToMustContainAllTokens($string): string
    {
        $string = self::stripPunctuation($string);
        $tokenizer = new WhitespaceAndPunctuationTokenizer();
        $tokens = $tokenizer->tokenize($string);
        array_walk($tokens, function(&$value) {
            if($value) {
                $value = '+' . $value;
            }
        });
        return implode(' ', $tokens);
    }

    public static function convertToAutoCompleteTerm($string): string
    {
        $string = self::stripPunctuation($string);
        $tokenizer = new WhitespaceAndPunctuationTokenizer();
        $tokens = $tokenizer->tokenize($string);
        $numTokens = count($tokens);
        if($numTokens) {
            $tokens[$numTokens - 1] .= "*";
        }
        return implode(' ', $tokens);
    }

    public static function normalize($string): string
    {
        $string = self::stripPunctuation($string);
        $tokenizer = new WhitespaceAndPunctuationTokenizer();
        $tokens = $tokenizer->tokenize($string);
        return implode(' ', $tokens);
    }
}
