<?php

namespace Shortcodes\Tests;

use Badcow\Shortcodes\Shortcodes;

class ShortcodesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $qbf = 'The quick brown fox jumps over the lazy dog';

    /**
     * Tests basic key value pair in attributes
     */
    public function testProcess_1()
    {
        $shortcodes = new Shortcodes;
        $shortcodes->addShortcode('test', array($this, 'dummyFunction_test'));

        $content = 'Hello my name is [test name="Sam"]!';
        $expectation = 'Hello my name is name: Sam!';

        $this->assertEquals($expectation, $shortcodes->process($content));
    }

    /**
     * Tests multiple shortcodes
     */
    public function testProcess_2()
    {
        $shortcodes = new Shortcodes;
        $shortcodes->addShortcode('test', array($this, 'dummyFunction_test'));
        $shortcodes->addShortcode('qbf', array($this, 'dummyFunction_qbf'));

        $content = 'Hello my name is [test name="Sam"]! Did you know that [qbf]';
        $expectation = 'Hello my name is name: Sam! Did you know that ' . $this->qbf;

        $this->assertEquals($expectation, $shortcodes->process($content));
    }

    /**
     * Tests enclosed shortcodes
     */
    public function testProcess_3()
    {
        $shortcodes = new Shortcodes;
        $shortcodes->addShortcode('enclosed', array($this, 'dummyFunction_enclosed'));

        $content = 'Hello [enclosed]my name is sam[/enclosed]';
        $expectation = 'Hello my name is sam';

        $this->assertEquals($expectation, $shortcodes->process($content));
    }

    /**
     * Tests behaviour of no shortcodes defined
     */
    public function testProcess_4()
    {
        $shortcodes = new Shortcodes;
        $content = 'Hello [enclosed]my name is sam[/enclosed]';

        $this->assertEquals($content, $shortcodes->process($content));
    }

    /**
     * Tests behaviour of self closed tags
     */
    public function testProcess_5()
    {
        $shortcodes = new Shortcodes;
        $shortcodes->addShortcode('enclosed', array($this, 'dummyFunction_enclosed'));

        $content = 'Hello [enclosed /]';
        $expectation = 'Hello ';

        $this->assertEquals($expectation, $shortcodes->process($content));
    }

    /**
     * Tests basic key value pair in attributes
     */
    public function testProcess_6()
    {
        $shortcodes = new Shortcodes;
        $shortcodes->addShortcode('test', array($this, 'dummyFunction_test'));

        $content = 'Hello my name is [test name=\'Sam\']! [test job=programmer]';
        $expectation = 'Hello my name is name: Sam! job: programmer';

        $this->assertEquals($expectation, $shortcodes->process($content));
    }

    /**
     * Tests basic key value pair in attributes
     */
    public function testProcess_7()
    {
        $shortcodes = new Shortcodes;
        $shortcodes->addShortcode('test', array($this, 'dummyFunction_test'));

        $content = 'Hello my name is [test "Sam"]! [test programmer]';
        $expectation = 'Hello my name is 0: Sam! 0: programmer';

        $this->assertEquals($expectation, $shortcodes->process($content));
    }

    /**
     * Tests basic key value pair in attributes
     */
    public function testProcess_8()
    {
        $shortcodes = new Shortcodes;
        $shortcodes->addShortcode('test', array($this, 'dummyFunction_test'));

        $content = 'Hello my name is [test \'Sam\']!';
        $expectation = 'Hello my name is 0: \'Sam\'!';

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

    public function testGetShortcodes()
    {
        $shortcodes = new Shortcodes;
        $shortcodes->addShortcode('test', array($this, 'dummyFunction_test'));

        $this->assertArrayHasKey('test', $shortcodes->getShortcodes());
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

        $content = 'Hello [enclosed]my name is sam[/enclosed] [[test]]';
        $expectation = 'Hello  [test]';

        $this->assertEquals($content, $shortcodes->stripAllShortcodes($content));

        $shortcodes->addShortcode('enclosed', array($this, 'dummyFunction_enclosed'));
        $shortcodes->addShortcode('test', array($this, 'dummyFunction_test'));

        $this->assertEquals($expectation, $shortcodes->stripAllShortcodes($content));
    }

    public function testRemoveShortcodes()
    {
        $shortcodes = new Shortcodes;
        $shortcodes->addShortcode('test', array($this, 'dummyFunction_test'));

        $this->assertTrue($shortcodes->hasShortcode('test'));
        $shortcodes->removeShortcode('test');
        $this->assertFalse($shortcodes->hasShortcode('test'));
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