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
        saveWikiText('control', file_get_contents(__DIR__ . '/data/pages/control.txt'), 'test');
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
        $hlp->markLesson('foo', true, 'test');
        $this->assertEquals(
            ['foo'],
            array_keys($hlp->getUserLessons('test'))
        );

        // add second lesson
        $hlp->markLesson('bar', true, 'test');
        $this->assertEquals(
            ['foo', 'bar'],
            array_keys($hlp->getUserLessons('test'))
        );

        // unmark first lesson
        $hlp->markLesson('foo', false, 'test');
        $this->assertEquals(
            ['bar'],
            array_keys($hlp->getUserLessons('test'))
        );
    }

    public function testParseControlPage()
    {
        $hlp = new \helper_plugin_lms();

        $result = $this->callInaccessibleMethod($hlp, 'parseControlPage', ['control']);
        $expected = [
            'this',
            'foo:bar',
            'another_link',
            'link',
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
