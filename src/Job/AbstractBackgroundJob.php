<?php

namespace App\LaravelBus\Job;

use App\Traits\Dispatchable\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;

abstract class DispatchableShouldQueue implements ShouldQueue
{
    use Dispatchable;
}
