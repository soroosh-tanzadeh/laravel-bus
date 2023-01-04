<?php

namespace Arvan\LaravelBus\Tests\Unit\Bus;

use Arvan\LaravelBus\Mocks\DispatcherMock;
use Arvan\LaravelBus\Tests\TestCase;
use Arvan\LaravelBus\Tests\Unit\Bus\TestJobs\MockJob;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Mockery\MockInterface;

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

}
