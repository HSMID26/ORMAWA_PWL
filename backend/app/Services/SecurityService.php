<?php

namespace App\Services;

class SecurityService
{
    /**
     * Allowed HTML tags for rich content (TipTap / WYSIWYG editor).
     */
    protected static array $allowedTags = [
        '<p>', '<h1>', '<h2>', '<h3>', '<h4>', '<h5>', '<h6>',
        '<b>', '<strong>', '<i>', '<em>', '<u>', '<s>', '<del>', '<strike>',
        '<ul>', '<ol>', '<li>', '<blockquote>', '<hr>', '<br>',
        '<a>', '<img>', '<table>', '<thead>', '<tbody>', '<tr>', '<th>', '<td>',
        '<code>', '<pre>', '<span>', '<div>', '<sub>', '<sup>', '<figure>', '<figcaption>'
    ];

    /**
     * Sanitize rich HTML content to prevent Cross-Site Scripting (XSS).
     *
     * @param string|null $html
     * @return string
     */
    public static function sanitizeHtml(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // 1. Remove dangerous script and iframe blocks entirely including inner content
        $cleaned = preg_replace('/<(script|iframe|object|embed|applet|style|form|svg|canvas|base|meta|link)[^>]*>.*?<\/\1>/is', '', $html);
        $cleaned = preg_replace('/<(script|iframe|object|embed|applet|style|form|svg|canvas|base|meta|link)[^>]*\/?>/is', '', $cleaned);

        // 2. Strip tags not in the allowed whitelist
        $cleaned = strip_tags($cleaned, implode('', self::$allowedTags));

        // 3. Remove inline javascript/event handlers (onload, onerror, onclick, onmouseover, etc.)
        $cleaned = preg_replace('/\s*on[a-zA-Z]+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/is', '', $cleaned);

        // 4. Remove javascript: or vbscript: or data: URI schemes from href and src attributes
        $cleaned = preg_replace_callback('/(href|src)\s*=\s*(["\'])(.*?)\2/is', function ($matches) {
            $attribute = $matches[1];
            $quote = $matches[2];
            $url = trim($matches[3]);

            // Decode HTML entities to detect hidden protocols like java&#115;cript:
            $decoded = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $decoded = preg_replace('/\s+/', '', $decoded);

            if (preg_match('/^(javascript|vbscript|data:text\/html):/i', $decoded)) {
                return "{$attribute}={$quote}#{$quote}";
            }

            return "{$attribute}={$quote}{$url}{$quote}";
        }, $cleaned);

        return $cleaned;
    }

    /**
     * Sanitize plain string input (strip tags and trim).
     *
     * @param string|null $string
     * @return string
     */
    public static function sanitizePlainString(?string $string): string
    {
        if (empty($string)) {
            return '';
        }

        return trim(strip_tags($string));
    }
}
