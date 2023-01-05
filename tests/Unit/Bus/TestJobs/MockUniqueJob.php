<?php

namespace Soroosh\LaravelBus\Tests\Unit\Bus\TestJobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Soroosh\LaravelBus\Job\DispatchableShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MockUniqueJob extends DispatchableShouldQueue implements ShouldBeUnique
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $uniqueFor;

    public function __construct()
    {
        $this->uniqueFor = 10;
    }

    public function uniqueId(): string
    {
        return static::class;
    }

    public function handle()
    {
        Log::info("Hi from job");
    }
}