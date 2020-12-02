<?php

namespace Z1px\Http\Tests\Server\Resetters;

use Mockery as m;
use Z1px\Http\Tests\TestCase;
use Z1px\Http\Server\Sandbox;
use Illuminate\Container\Container;
use Z1px\Http\Server\Resetters\ClearInstances;

class ClearInstanceTest extends TestCase
{
    public function testClearInstance()
    {
        $sandbox = m::mock(Sandbox::class);
        $sandbox->shouldReceive('getConfig->get')
                ->with('swoole_http.instances', [])
                ->once()
                ->andReturn(['foo']);

        $container = new Container;
        $container->instance('foo', m::mock('foo'));

        $resetter = new ClearInstances;
        $app = $resetter->handle($container, $sandbox);

        $this->assertFalse($app->bound('foo'));
    }
}
