![Test](https://github.com/soroosh-tanzadeh/laravel-bus/actions/workflows/php.yml/badge.svg)

# Laravel Bus
Enhanced laravel Illuminate\Bus.

- Log jobs when dispatch fails

## How to use

Create a new job
```bash
  php artisan laravel-bus:make-job DummyJob 
```

Or just extends ``Soroosh\LaravelBus\Job\DispatchableShouldQueue`` in existing job:

```php
<?php
namespace App\Jobs;

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
        Log::info("Hello from MockJob");
    }
}
```