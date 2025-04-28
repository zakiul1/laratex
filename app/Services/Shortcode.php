<?php

namespace App\Services;

class Shortcode
{
    /** @var array<string,callable> */
    protected static array $tags = [];

    /**
     * Register a new shortcode handler.
     */
    public static function add(string $tag, callable $callback): void
    {
        static::$tags[$tag] = $callback;
    }

    /**
     * Parse a string of content, replacing all [tag attrs…] with their callbacks.
     */
    public static function compile(string $content): string
    {
        return preg_replace_callback(
            '/\[(\w+)([^\]]*)\]/',
            function ($match) {
                $tag = $match[1];
                $attrString = $match[2] ?? '';
                if (!isset(static::$tags[$tag])) {
                    return $match[0];
                }
                $attrs = self::parseAttributes($attrString);
                return call_user_func(static::$tags[$tag], $attrs);
            },
            $content
        );
    }

    /**
     * Convert key="value" pairs into an associative array.
     */
    protected static function parseAttributes(string $text): array
    {
        $attrs = [];
        if (preg_match_all('/(\w+)=["\']([^"\']+)["\']/', $text, $m)) {
            foreach ($m[1] as $i => $key) {
                $attrs[$key] = $m[2][$i];
            }
        }
        return $attrs;
    }
}