<?php

namespace Z1px\Http\Tests\Server\Resetters;

use Mockery as m;
use Z1px\Http\Tests\TestCase;
use Z1px\Http\Server\Sandbox;
use Illuminate\Container\Container;
use Z1px\Http\Server\Resetters\ResetSession;

class ResetSessionTest extends TestCase
{
    public function testResetSession()
    {
        $session = m::mock('session');
        $session->shouldReceive('flush')
                ->once();
        $session->shouldReceive('regenerate')
                ->once();

        $sandbox = m::mock(Sandbox::class);

        $container = new Container;
        $container->instance('session', $session);

        $resetter = new ResetSession;
        $app = $resetter->handle($container, $sandbox);
    }
}
