<?php

namespace nexus4d\api;

/**
 * Local dummy text generator.
 *
 * No external API connection is required.
 */
class LipsumGenerator
{
    private function __construct() {}
    private function __clone() {}

    private const WORDS = [
        'lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur',
        'adipiscing', 'elit', 'integer', 'nec', 'odio', 'praesent',
        'libero', 'sed', 'cursus', 'ante', 'dapibus', 'diam',
        'sed', 'nisi', 'nulla', 'quis', 'sem', 'at', 'nibh',
        'elementum', 'imperdiet', 'duis', 'sagittis', 'ipsum',
        'praesent', 'mauris', 'fusce', 'nec', 'tellus', 'sed',
        'augue', 'semper', 'porta', 'maecenas', 'massa', 'vestibulum',
        'lacinia', 'arcu', 'eget', 'nulla', 'class', 'aptent',
        'taciti', 'sociosqu', 'ad', 'litora', 'torquent', 'per',
        'conubia', 'nostra', 'inceptos', 'himenaeos', 'curabitur',
        'sodales', 'ligula', 'in', 'libero', 'vivamus', 'euismod',
        'mauris', 'varius', 'quam', 'quisque', 'velit', 'nisi',
        'porta', 'eget', 'aliquet', 'nec', 'imperdiet', 'at',
        'urna', 'nullam', 'vitae', 'libero', 'ac', 'risus',
        'placerat', 'mattis', 'vestibulum', 'commodo', 'felis',
        'quis', 'tortor', 'donec', 'id', 'elit', 'non', 'mi',
        'porta', 'gravida', 'at', 'eget', 'metus'
    ];

    /**
     * Generate paragraphs of dummy text.
     */
    public static function getParagraphs($amount = 5, $start = true)
    {
        $amount = max(1, (int) $amount);

        $paragraphs = [];

        for ($i = 0; $i < $amount; $i++) {
            $wordCount = random_int(40, 80);

            $paragraphs[] = self::generateText(
                $wordCount,
                $start && $i === 0
            );
        }

        return nl2br(implode("\n\n", $paragraphs));
    }

    /**
     * Generate a given amount of words.
     */
    public static function getWords($amount = 5, $start = true)
    {
        $amount = max(1, (int) $amount);

        return nl2br(
            self::generateText($amount, $start)
        );
    }

    /**
     * Generate a given amount of bytes.
     */
    public static function getBytes($amount = 27, $start = true)
    {
        $amount = max(27, (int) $amount);

        $text = '';

        while (strlen($text) < $amount) {
            $text .= self::generateText(
                random_int(5, 12),
                $start && $text === ''
            ) . ' ';
        }

        return nl2br(
            substr(trim($text), 0, $amount)
        );
    }

    /**
     * Generate lists.
     */
    public static function getLists($amount = 5, $start = true)
    {
        $amount = max(1, (int) $amount);

        $lists = [];

        for ($i = 0; $i < $amount; $i++) {
            $items = [];

            $itemCount = random_int(3, 7);

            for ($j = 0; $j < $itemCount; $j++) {
                $items[] = self::generateText(
                    random_int(5, 12),
                    $start && $i === 0 && $j === 0
                ) . '.';
            }

            $lists[] = $items;
        }

        return $lists;
    }

    /**
     * Generate random dummy text.
     */
    private static function generateText($amount, $start)
    {
        $amount = max(1, (int) $amount);

        $words = [];

        for ($i = 0; $i < $amount; $i++) {
            $words[] = self::WORDS[
                array_rand(self::WORDS)
            ];
        }

        $text = implode(' ', $words);

        if ($start) {
            $prefix = 'Lorem ipsum dolor sit amet';

            $remaining = max(
                0,
                $amount - 5
            );

            if ($remaining > 0) {
                $rest = [];

                for ($i = 0; $i < $remaining; $i++) {
                    $rest[] = self::WORDS[
                        array_rand(self::WORDS)
                    ];
                }

                $text = $prefix . ' ' . implode(' ', $rest);
            } else {
                $text = $prefix;
            }
        }

        return ucfirst(trim($text));
    }
}