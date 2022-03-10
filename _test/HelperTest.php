<?php

namespace dokuwiki\plugin\lms\test;

use DokuWikiTest;

/**
 * Helper tests for the lms plugin
 *
 * @group plugin_lms
 * @group plugins
 */
class HelperTest extends DokuWikiTest
{
    public function setUp(): void
    {
        parent::setUp();
        saveWikiText('lms', file_get_contents(__DIR__ . '/data/pages/lms.txt'), 'test');
        saveWikiText('foo:lms', file_get_contents(__DIR__ . '/data/pages/lms.txt'), 'test');
    }

    public function testMarkRead()
    {
        $hlp = new \helper_plugin_lms();

        // no lessons, yet
        $this->assertEquals(
            [],
            array_keys($hlp->getUserLessons('test'))
        );

        // add lesson
        $hlp->markLesson('foo', 'test', true);
        $this->assertEquals(
            ['foo'],
            array_keys($hlp->getUserLessons('test'))
        );

        // add second lesson
        $hlp->markLesson('bar', 'test', true);
        $this->assertEquals(
            ['foo', 'bar'],
            array_keys($hlp->getUserLessons('test'))
        );

        // unmark first lesson
        $hlp->markLesson('foo', 'test', false);
        $this->assertEquals(
            ['bar'],
            array_keys($hlp->getUserLessons('test'))
        );
    }

    public function testNextLesson() {
        $hlp = new \helper_plugin_lms();

        $result = $hlp->getNextLesson('nope');
        $this->assertEquals(false, $result, '$id is not a lesson');

        $result = $hlp->getNextLesson('link');
        $this->assertEquals(false, $result, '$id is last lesson');

        $result = $hlp->getNextLesson('this');
        $this->assertEquals('foo:bar', $result, 'next lesson no user context');

        $hlp->markLesson('foo:bar', 'test', true);
        $result = $hlp->getNextLesson('this', 'test');
        $this->assertEquals('another_link', $result, 'skip seen lesson');
    }

    public function testPrevLesson() {
        $hlp = new \helper_plugin_lms();

        $result = $hlp->getPrevLesson('nope');
        $this->assertEquals(false, $result, '$id is not a lesson');

        $result = $hlp->getPrevLesson('this');
        $this->assertEquals(false, $result, '$id is first lesson');

        $result = $hlp->getPrevLesson('another_link');
        $this->assertEquals('foo:bar', $result, 'prev lesson no user context');

        $hlp->markLesson('foo:bar', 'test', true);
        $result = $hlp->getPrevLesson('another_link', 'test');
        $this->assertEquals('this', $result, 'skip seen lesson');
    }

    public function testParseTopControlPage()
    {
        $hlp = new \helper_plugin_lms();

        $result = $this->callInaccessibleMethod($hlp, 'parseControlPage', ['lms']);
        $expected = [
            'this',
            'foo:bar',
            'another_link',
            'relativeup',
            'foo2:this',
            'blarg:down',
            'toplevel',
            'link',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testParseNsControlPage()
    {
        $hlp = new \helper_plugin_lms();

        $result = $this->callInaccessibleMethod($hlp, 'parseControlPage', ['foo:lms']);
        $expected = [
            'foo:this',
            'foo:bar',
            'foo:another_link',
            'foo:blarg:down',
            'foo:link',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testParseControlPageMissing()
    {
        $hlp = new \helper_plugin_lms();

        $result = $this->callInaccessibleMethod($hlp, 'parseControlPage', ['nope']);
        $expected = [];

        $this->assertEquals($expected, $result);
    }
}
