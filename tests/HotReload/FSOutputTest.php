<?php

namespace Z1px\Http\Tests\HotReload;

use Z1px\Http\HotReload\FSEventParser;
use Z1px\Http\HotReload\FSOutput;
use Z1px\Http\Tests\TestCase;

/**
 * Class FSOutputTest
 */
class FSOutputTest extends TestCase
{
    public function testItFormatOutputCorrectly()
    {
        $buffer = 'Mon Dec 31 01:18:34 2018 /Some/Path/To/File/File.php Renamed OwnerModified IsFile';
        $event = FSEventParser::toEvent($buffer);

        $this->assertEquals(
            'File: /Some/Path/To/File/File.php OwnerModified, Renamed at 2018.12.31 01:18:34',
            FSOutput::format($event)
        );
    }
}