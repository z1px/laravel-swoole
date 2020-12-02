<?php

namespace Z1px\Http\Server\Resetters;

use Illuminate\Contracts\Container\Container;
use Z1px\Http\Server\Sandbox;

interface ResetterContract
{
    /**
     * "handle" function for resetting app.
     *
     * @param \Illuminate\Contracts\Container\Container $app
     * @param \Z1px\Http\Server\Sandbox $sandbox
     */
    public function handle(Container $app, Sandbox $sandbox);
}
