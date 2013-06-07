Badcow Shortcodes
=================

This is a port of WordPress' brilliant shortcode feature for use outside of WordPress. The code has remained largely
unchanged.

The purpose of this project is to use the familiar WordPress shortcode syntax in any system

Basic Usage
-----------

    $shortcodes = new Badcow\Shortcodes\Shortcodes;
    $shortcodes->addShortcode('hello', function ($attributes, $content, $tagName) {
        return $attributes['greeting'] . ', ' . $content;
    });

    echo $shortcodes->process('My shortcode does this: [hello greeting="Konnichiwa"]world![/hello]');

Installation
------------

Use [Composer](http://getcomposer.org/). That's what all the cool kids use.

    "require" : { "badcow/shortcodes": "dev-master" }

Running Tests
-------------

    phpunit /path/to/shortcodes
