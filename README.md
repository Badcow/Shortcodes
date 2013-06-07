Badcow Shortcodes
=================

This is a port of WordPress' brilliant shortcode feature for use outside of WordPress. The code has remained largely unchanged

Basic Usage
-----------

    $shortcodes = new Badcow\Shortcodes\Shortcodes;
    $shortcodes->addShortcode('hello', function ($attributes, $content, $tagName) {
        return $attributes['greeting'] . ', ' . $content;
    });

    echo $shortcodes->process('My shortcode does this: [hello greeting="Konnichiwa"]world![/hello]');