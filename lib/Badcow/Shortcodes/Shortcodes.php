<?php

namespace Badcow\Shortcodes;

/**
 * This is a port of WordPress' brilliant shortcode feature
 * for use outside of WordPress. The code has remained largely unchanged
 *
 * Class Shortcodes
 *
 * @package Shortcodes
 */
class Shortcodes
{
    /**
     * The regex for attributes.
     *
     * This regex covers the following attribute situations:
     *  - key = "value"
     *  - key = 'value'
     *  - key = value
     *  - "value"
     *  - value
     *
     * @var string
     */
    private $attrPattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';

    /**
     * Indexed array of tags: shortcode callbacks
     *
     * @var array
     */
    private $shortcodes = array();

    /**
     * @param string $tag
     * @param callable $function
     * @throws \ErrorException
     */
    public function addShortcode($tag, $function)
    {
        if (!is_callable($function)) {
            throw new \ErrorException("Function must be callable");
        }

        $this->shortcodes[$tag] = $function;
    }

    /**
     * @param string $tag
     */
    public function removeShortcode($tag)
    {
        if (array_key_exists($tag, $this->shortcodes)) {
            unset($this->shortcodes[$tag]);
        }
    }

    /**
     * @return array
     */
    public function getShortcodes()
    {
        return $this->shortcodes;
    }

    /**
     * @param $shortcode
     * @return bool
     */
    public function hasShortcode($shortcode)
    {
        return array_key_exists($shortcode, $this->shortcodes);
    }

    /**
     * Tests whether content has a particular shortcode
     *
     * @param $content
     * @param $tag
     * @return bool
     */
    public function contentHasShortcode($content, $tag)
    {
        if (!$this->hasShortcode($tag)) {
            return false;
        }

        preg_match_all($this->shortcodeRegex(), $content, $matches, PREG_SET_ORDER );

        if (empty($matches)) {
            return false;
        }

        foreach ($matches as $shortcode) {
            if ($tag === $shortcode[2]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve the shortcode regular expression for searching.
     *
     * The regular expression combines the shortcode tags in the regular expression
     * in a regex class.
     *
     * The regular expression contains 6 different sub matches to help with parsing.
     *
     * 1 - An extra [ to allow for escaping shortcodes with double [[]]
     * 2 - The shortcode name
     * 3 - The shortcode argument list
     * 4 - The self closing /
     * 5 - The content of a shortcode when it wraps some content.
     * 6 - An extra ] to allow for escaping shortcodes with double [[]]
     *
     * @return string The shortcode search regular expression
     */
    private function shortcodeRegex()
    {
        $tagRegex = join('|', array_map('preg_quote', array_keys($this->shortcodes)));

        return
              '/'
            . '\\['                              // Opening bracket
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tagRegex)"                      // 2: Shortcode name
            . '(?![\\w-])'                       // Not followed by word character or hyphen
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
            .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
            .     '(?:'
            .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
            .         '[^\\]\\/]*'               // Not a closing bracket or forward slash
            .     ')*?'
            . ')'
            . '(?:'
            .     '(\\/)'                        // 4: Self closing tag ...
            .     '\\]'                          // ... and closing bracket
            . '|'
            .     '\\]'                          // Closing bracket
            .     '(?:'
            .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            .             '[^\\[]*+'             // Not an opening bracket
            .             '(?:'
            .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            .                 '[^\\[]*+'         // Not an opening bracket
            .             ')*+'
            .         ')'
            .         '\\[\\/\\2\\]'             // Closing shortcode tag
            .     ')?'
            . ')'
            . '(\\]?)'                           // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
            . '/s';
    }

    /**
     * Search content for shortcodes and filter shortcodes through their hooks.
     *
     * If there are no shortcode tags defined, then the content will be returned
     * without any filtering. This might cause issues when plugins are disabled but
     * the shortcode will still show up in the post or content.
     *
     * @param string $content Content to search for shortcodes
     * @return string Content with shortcodes filtered out.
     */
    public function process($content)
    {
        if (empty($this->shortcodes)) {
            return $content;
        }

        return preg_replace_callback($this->shortcodeRegex(), array($this, 'processTag'), $content);
    }

    /**
     * Regular Expression callable for do_shortcode() for calling shortcode hook.
     * @see get_shortcode_regex for details of the match array contents.
     *
     * @param array $tag Regular expression match array
     * @return mixed False on failure.
     */
    private function processTag(array $tag)
    {
        // allow [[foo]] syntax for escaping a tag
        if ( $tag[1] == '[' && $tag[6] == ']' ) {
            return substr($tag[0], 1, -1);
        }

        $tagName = $tag[2];
        $attr = $this->parseAttributes($tag[3]);

        if ( isset( $tag[5] ) ) {
            // enclosing tag - extra parameter
            return $tag[1] . call_user_func($this->shortcodes[$tagName], $attr, $tag[5], $tagName) . $tag[6];
        } else {
            // self-closing tag
            return $tag[1] . call_user_func($this->shortcodes[$tagName], $attr, null,  $tagName) . $tag[6];
        }
    }

    /**
     * Retrieve all attributes from the shortcodes tag.
     *
     * The attributes list has the attribute name as the key and the value of the
     * attribute as the value in the key/value pair. This allows for easier
     * retrieval of the attributes, since all attributes have to be known.
     *
     *
     * @param string $text
     * @return array List of attributes and their value.
     */
    public function parseAttributes($text)
    {
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);

        if (!preg_match_all($this->attrPattern, $text, $matches, PREG_SET_ORDER)) {
            return array(ltrim($text));
        }

        $attr = array();

        foreach ($matches as $match) {
            if (!empty($match[1])) {
                $attr[strtolower($match[1])] = stripcslashes($match[2]);
            } elseif (!empty($match[3])) {
                $attr[strtolower($match[3])] = stripcslashes($match[4]);
            } elseif (!empty($match[5])) {
                $attr[strtolower($match[5])] = stripcslashes($match[6]);
            } elseif (isset($match[7]) and strlen($match[7])) {
                $attr[] = stripcslashes($match[7]);
            } elseif (isset($match[8])) {
                $attr[] = stripcslashes($match[8]);
            }
        }

        return $attr;
    }


    /**
     * Remove all shortcode tags from the given content.
     *
     * @since 2.5
     * @uses $shortcode_tags
     *
     * @param string $content Content to remove shortcode tags.
     * @return string Content without shortcode tags.
     */
    public function stripAllShortcodes($content)
    {
        if (empty($this->shortcodes)) {
            return $content;
        }

        return preg_replace_callback($this->shortcodeRegex(), array($this, 'stripShortcodeTag'), $content);
    }

    private function stripShortcodeTag($tag)
    {
        // allow [[foo]] syntax for escaping a tag
        if ( $tag[1] == '[' && $tag[6] == ']' ) {
            return substr($tag[0], 1, -1);
        }

        return $tag[1] . $tag[6];
    }
}