<?php

namespace Soroosh\LaravelBus\Console;

use Symfony\Component\Console\Input\InputOption;
use Illuminate\Console\GeneratorCommand;

class MakeJob extends GeneratorCommand
{
    protected $name = 'laravel-bus:make-job';

    protected $description = 'Create a new job class';

    protected $type = 'Class';

    protected function getStub()
    {
        return __DIR__ . '/stubs/class.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . "\\Jobs";
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the class already exists'],
            ['constructor', 'c', InputOption::VALUE_NONE, 'Create a new class with constructor'],
        ];
    }
}
