<?php
namespace App\Helpers;

class BlockRenderer
{
    /**
     * Turn your stored block-JSON into HTML.
     *
     * @param  string  $raw  The JSON string stored in content
     * @return string        The rendered HTML
     */
    public static function render(string $raw): string
    {
        // 1) Remove any leading/trailing slashes or quotes
        $json = trim($raw, "\\\"");

        // 2) Decode to PHP array
        $blocks = json_decode($json, true);
        if (!is_array($blocks)) {
            return ''; // invalid JSON
        }

        // 3) Render each block
        $html = '';
        foreach ($blocks as $block) {
            $html .= static::renderBlock($block);
        }

        return $html;
    }

    protected static function renderBlock(array $block): string
    {
        switch ($block['type'] ?? '') {
            case 'Area':
                $children = '';
                foreach ($block['childs'] ?? [] as $child) {
                    $children .= static::renderBlock($child);
                }
                return "<div style=\"display:flex;flex-direction:{$block['direction']};width:{$block['width']}%\">{$children}</div>";

            case 'P':
                $text = htmlspecialchars($block['content'] ?? '');
                return "<p>{$text}</p>";

            // add more block types here as you need...

            default:
                return '';
        }
    }
}