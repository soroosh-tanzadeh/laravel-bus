<?php

namespace Arvan\LaravelBus\Mocks;

use Illuminate\Contracts\Bus\Dispatcher;

class DispatcherMock implements Dispatcher
{
    /**
     * @throws \Exception
     */
    public function dispatch($command)
    {
        throw new \Exception();
    }

    public function dispatchSync($command, $handler = null)
    {
        $command->handle();
    }

    public function dispatchNow($command, $handler = null)
    {
        $command->handle();
    }

    public function hasCommandHandler($command)
    {
        return true;
    }

    /**
     * @throws \Exception
     */
    public function getCommandHandler($command)
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @throws \Exception
     */
    public function pipeThrough(array $pipes)
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @throws \Exception
     */
    public function map(array $map)
    {
        throw new \Exception("Not implemented");
    }
}
