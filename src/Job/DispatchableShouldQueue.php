<?php

namespace Arvan\LaravelBus\Job;

use Arvan\LaravelBus\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;

abstract class DispatchableShouldQueue implements ShouldQueue
{
    use Dispatchable;
}
