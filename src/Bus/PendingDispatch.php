<?php

namespace Soroosh\LaravelBus\Bus;

use Throwable;
use RedisException;
use Illuminate\Support\Str;
use Illuminate\Bus\UniqueLock;
use Illuminate\Container\Container;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Illuminate\Foundation\Bus\PendingDispatch as BusPendingDispatch;

class PendingDispatch extends BusPendingDispatch
{
    private function jobShouldBeEncrypted($job): bool
    {
        if ($job instanceof ShouldBeEncrypted) {
            return true;
        }

        return isset($job->shouldBeEncrypted) && $job->shouldBeEncrypted;
    }

    private function createJobPayload(): array
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
     * Determine if the job should be dispatched.
     *
     * @return bool
     */
    protected function shouldDispatch()
    {
        if (!$this->job instanceof ShouldBeUnique) {
            return true;
        }

        $redisLock = false;
        try {
            $redisLock = (new UniqueLock(Container::getInstance()->make(Cache::class)))
                ->acquire($this->job);
        } catch (\RedisException $redisException) {
            report($redisException);
        }

        $dbLock = (new UniqueLock(\Illuminate\Support\Facades\Cache::driver(config("laravel-bus.alternative_cache_driver"))))
            ->acquire($this->job);

        return $redisLock || $dbLock;
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
        } catch (RedisException $th) {
            $defaultConnection = config('queue.default');
            $defaultConnectionSettings = config("queue.connections.$defaultConnection");
            $queue = $this->job->queue ?? ($defaultConnectionSettings['queue'] ?? "default");

            /** @var FailedJobProviderInterface $queueFailedJobProvider */
            $queueFailedJobProvider = app("queue.failer");

            $queueFailedJobProvider->log(
                $this->job->connection ?? $defaultConnection,
                $queue,
                json_encode($this->createJobPayload()),
                $th
            );
            report($th);
        }
    }
}
