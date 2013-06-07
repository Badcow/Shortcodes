<?php

namespace Shortcodes\Tests;

use Shortcodes\Shortcodes;

class ShortcodesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $qbf = 'The quick brown fox jumps over the lazy dog';

    public function testProcess_1()
    {
        $shortcodes = new Shortcodes;
        $shortcodes->addShortcode('test', array($this, 'dummyFunction_test'));

        $content = 'Hello my name is [test name="Sam"]!';
        $expectation = 'Hello my name is name: Sam!';

        $this->assertEquals($expectation, $shortcodes->process($content));
    }

    public function testProcess_2()
    {
        $shortcodes = new Shortcodes;
        $shortcodes->addShortcode('test', array($this, 'dummyFunction_test'));
        $shortcodes->addShortcode('qbf', array($this, 'dummyFunction_qbf'));

        $content = 'Hello my name is [test name="Sam"]! Did you know that [qbf]';
        $expectation = 'Hello my name is name: Sam! Did you know that ' . $this->qbf;

        $this->assertEquals($expectation, $shortcodes->process($content));
    }

    public function testProcess_3()
    {
        $shortcodes = new Shortcodes;
        $shortcodes->addShortcode('enclosed', array($this, 'dummyFunction_enclosed'));

        $content = 'Hello [enclosed]my name is sam[/enclosed]';
        $expectation = 'Hello my name is sam';

        $this->assertEquals($expectation, $shortcodes->process($content));
    }

    /**
     * @expectedException \ErrorException
     */
    public function testAddShortcode()
    {
        $shortcodes = new Shortcodes;
        $shortcodes->addShortcode('enclosed','foobar');
    }

    public function testHasShortcode()
    {
        $shortcodes = new Shortcodes;
        $shortcodes->addShortcode('test', array($this, 'dummyFunction_test'));

        $this->assertTrue($shortcodes->hasShortcode('test'));
        $this->assertFalse($shortcodes->hasShortcode('foobar'));
    }

    public function testContentHasShortcode()
    {
        $shortcodes = new Shortcodes;
        $shortcodes->addShortcode('test', array($this, 'dummyFunction_test'));
        $content1 = 'Hello my name is [test name="Sam"]!';
        $content2 = 'Hello my name is Sam!';

        $this->assertTrue($shortcodes->contentHasShortcode($content1, 'test'));
        $this->assertFalse($shortcodes->contentHasShortcode($content1, 'foobar'));

        $this->assertFalse($shortcodes->contentHasShortcode($content2, 'foobar'));
    }

    public function testEscaping()
    {
        $shortcodes = new Shortcodes;
        $shortcodes->addShortcode('test', array($this, 'dummyFunction_test'));
        $content = 'Hello my name is [[test name="Sam"]]!';
        $expectation = 'Hello my name is [test name="Sam"]!';

        $this->assertEquals($expectation, $shortcodes->process($content));
    }

    public function testStripAllShortcodes()
    {
        $shortcodes = new Shortcodes;
        $shortcodes->addShortcode('enclosed', array($this, 'dummyFunction_enclosed'));

        $content = 'Hello [enclosed]my name is sam[/enclosed]';
        $expectation = 'Hello ';

        $this->assertEquals($expectation, $shortcodes->stripAllShortcodes($content));
    }

    public function dummyFunction_test(array $attributes)
    {
        $returnStr = '';
        foreach ($attributes as $key => $attr) {
            $returnStr .= "$key: $attr";
        }

        return $returnStr;
    }

    public function dummyFunction_qbf(array $attributes)
    {
        return $this->qbf;
    }

    public function  dummyFunction_enclosed(array $attributes, $content, $tagName)
    {
        return $content;
    }
}