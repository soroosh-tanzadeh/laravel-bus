<?php

namespace Soroosh\LaravelBus\Job;

use Soroosh\LaravelBus\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;

abstract class DispatchableShouldQueue implements ShouldQueue
{
    use Dispatchable;
}
