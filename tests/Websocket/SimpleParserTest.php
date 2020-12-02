<?php

namespace Z1px\Http\Tests\Websocket;

use Mockery as m;
use Swoole\Websocket\Frame;
use Z1px\Http\Tests\TestCase;
use Z1px\Http\Websocket\SimpleParser;

class SimpleParserTest extends TestCase
{
    public function testEncode()
    {
        $event = 'foo';
        $data = 'bar';

        $parser = new SimpleParser;
        $this->assertSame(json_encode([
            'event' => $event,
            'data' => $data,
        ]), $parser->encode($event, $data));
    }

    public function testDecode()
    {
        $payload = json_encode($data = [
            'event' => 'foo',
            'data' => 'bar',
        ]);
        $frame = m::mock(Frame::class);
        $frame->data = $payload;

        $parser = new SimpleParser;
        $this->assertSame($data, $parser->decode($frame));
    }
}
