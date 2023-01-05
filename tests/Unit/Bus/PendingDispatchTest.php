<?php

namespace Soroosh\LaravelBus\Tests\Unit\Bus;

use Illuminate\Bus\UniqueLock;
use Illuminate\Support\Facades\Cache;
use Soroosh\LaravelBus\Mocks\DispatcherMock;
use Soroosh\LaravelBus\Tests\TestCase;
use Soroosh\LaravelBus\Tests\Unit\Bus\TestJobs\MockJob;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Mockery\MockInterface;
use Soroosh\LaravelBus\Tests\Unit\Bus\TestJobs\MockUniqueJob;

class PendingDispatchTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        app()->extend(Dispatcher::class, fn() => new DispatcherMock());
    }

    public function test_shouldLogWhenFailsToDispatchJob()
    {
        $this->instance("queue.failer", \Mockery::mock(FailedJobProviderInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive("log")->withArgs(function ($connection, $queue, $payload, $exception) {
                $jobPayload = json_decode($payload);
                $this->assertEquals(MockJob::class, $jobPayload->displayName);
                return true;
            });
        }));
        MockJob::dispatch();
    }

    public function test_shouldNotDispatchJobWhenJobIsLocked(){
        // Lock Job using database cache
        (new UniqueLock(Cache::driver(config("laravel-bus.alternative_cache_driver"))))
            ->acquire(new MockUniqueJob());

        $this->instance("queue.failer", \Mockery::mock(FailedJobProviderInterface::class, function (MockInterface $mock) {
            $mock->shouldNotReceive("log")->withArgs(function ($connection, $queue, $payload, $exception) {
                $jobPayload = json_decode($payload);
                $this->assertEquals(MockJob::class, $jobPayload->displayName);
                return true;
            });
        }));
        MockUniqueJob::dispatch();
    }

}
