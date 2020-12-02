<?php

namespace Z1px\Http\Tests\HotReload;

use Carbon\Carbon;
use Z1px\Http\HotReload\FSEvent;
use Z1px\Http\HotReload\FSEventParser;
use Z1px\Http\Tests\TestCase;

/**
 * Class FSEventParserTest
 */
class FSEventParserTest extends TestCase
{
    public function testItCanCreateObjectAfterParse()
    {
        $buffer = 'Mon Dec 31 01:18:34 2018 /Some/Path/To/File/File.php Renamed OwnerModified IsFile';
        $event = FSEventParser::toEvent($buffer);

        $this->assertInstanceOf(FSEvent::class, $event);

        $this->assertTrue(array_diff($event->getTypes(), [FSEvent::Renamed, FSEvent::OwnerModified]) === []);
        $this->assertTrue((new Carbon('Mon Dec 31 01:18:34 2018'))->eq($event->getWhen()));
        $this->assertEquals('/Some/Path/To/File/File.php', $event->getPath());
        $this->assertTrue($event->isType(FSEvent::Renamed));
        $this->assertTrue($event->isType(FSEvent::OwnerModified));
    }
}