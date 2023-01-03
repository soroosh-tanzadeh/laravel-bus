<?php

namespace App\LaravelBus\Bus;

use Illuminate\Support\Str;
use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Illuminate\Foundation\Bus\PendingDispatch as BusPendingDispatch;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Bus\Dispatcher;

class PendingDispatch extends BusPendingDispatch
{
    private function jobShouldBeEncrypted($job)
    {
        if ($this->job instanceof ShouldBeEncrypted) {
            return true;
        }

        return isset($job->shouldBeEncrypted) && $job->shouldBeEncrypted;
    }

    private function createJobPayload($queue)
    {
        $uuid = Str::uuid();
        $jobClass = get_class($this->job);
        $tags = method_exists($this->job, "tags") ? $this->job->tags() : [
            "$jobClass:" . Str::uuid()
        ];

        /** @var \Illuminate\Queue\Queue $queueConnection */
        $queueConnection = app("queue.connection");

        $container = $queueConnection->getContainer();

        $command = $this->jobShouldBeEncrypted($this->job) && $container->bound(Encrypter::class)
            ? $container[Encrypter::class]->encrypt(serialize(clone $this->job))
            : serialize(clone $this->job);

        return [
            'uuid' => $uuid,
            'id' => $uuid,
            'tags' => $tags,
            'pushedAt' => now()->milliseconds(),
            'displayName' => $jobClass,
            'maxTries' => $this->job->tries ?? null,
            'backoff' => $queueConnection->getJobBackoff($this->job),
            'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
            'maxExceptions' => $this->job->maxExceptions ?? null,
            'failOnTimeout' => $this->job->failOnTimeout ?? false,
            'data' => [
                'command' => $command,
                'commandName' => get_class($this->job)
            ],
            'type' => 'job'
        ];
    }


    /**
     * Handle the object's destruction.
     *
     * @return void
     */
    public function __destruct()
    {
        try {
            if (!$this->shouldDispatch()) {
                return;
            } elseif ($this->afterResponse) {
                app(Dispatcher::class)->dispatchAfterResponse($this->job);
            } else {
                app(Dispatcher::class)->dispatch($this->job);
            }
        } catch (\Throwable $th) {
            $defaultConnection = config('queue.default');
            $defaultConnetionSettings = config("queue.connections.$defaultConnection");
            $queue = $this->job->queue ?? $defaultConnetionSettings['queue'];

            /** @var FailedJobProviderInterface $queueFailedJobProvider */
            $queueFailedJobProvider = app("queue.failer");

            $queueFailedJobProvider->log(
                $this->job->connection ?? $defaultConnection,
                $queue,
                json_encode($this->createJobPayload($queue)),
                $th
            );
        }
    }
}
