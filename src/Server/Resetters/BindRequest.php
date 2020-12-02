<?php

namespace Z1px\Http\Server\Resetters;

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Z1px\Http\Server\Sandbox;

/**
 * Class BindRequest
 */
class BindRequest implements ResetterContract
{
    /**
     * "handle" function for resetting app.
     *
     * @param \Illuminate\Contracts\Container\Container $app
     * @param \Z1px\Http\Server\Sandbox $sandbox
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    public function handle(Container $app, Sandbox $sandbox)
    {
        $request = $sandbox->getRequest();

        if ($request instanceof Request) {
            $app->instance('request', $request);
        }

        return $app;
    }
}
