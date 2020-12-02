<?php

namespace Z1px\Http\Server\Resetters;

use Illuminate\Contracts\Container\Container;
use Z1px\Http\Server\Sandbox;

class ResetCookie implements ResetterContract
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
        if (isset($app['cookie'])) {
            $cookies = $app->make('cookie');
            foreach ($cookies->getQueuedCookies() as $key => $value) {
                $cookies->unqueue($value->getName());
            }
        }

        return $app;
    }
}
