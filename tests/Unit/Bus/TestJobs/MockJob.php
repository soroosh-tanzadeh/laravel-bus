<?php

namespace Soroosh\LaravelBus\Tests\Unit\Bus\TestJobs;

use Soroosh\LaravelBus\Job\DispatchableShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MockJob extends DispatchableShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle()
    {
        Log::info("Hi from job");
    }
}