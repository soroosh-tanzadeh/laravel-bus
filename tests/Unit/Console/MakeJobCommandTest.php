<?php

namespace Arvan\Bus\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use Soroosh\LaravelBus\Tests\TestCase;
use Soroosh\LaravelBus\LaravelBusServiceProvider;

class MakeJobCommandTest extends TestCase
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Class name.
     *
     * @var string
     */
    protected $classname;

    /**
     * Class file path.
     *
     * @var string
     */
    protected $filepath;

    /**
     * Load package service provider
     * @param  \Illuminate\Foundation\Application $app
     * @return Soroosh\LaravelBus\LaravelBusServiceProvider
     */
    protected function getPackageProviders($app)
    {
        return [LaravelBusServiceProvider::class];
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->files = new Filesystem();
        $this->classname = 'TestJob';
        $this->filepath = $this->app['path'] . '/Jobs/' . $this->classname . '.php';
    }

    /**
     * Test for creating a new class from artisan command.
     *
     * @test
     */
    public function test_ShouldCreateJobSuccessfully()
    {
        $this->artisan('laravel-bus:make-job', ['name' => $this->classname, '--force' => true]);

        $class_content = $this->files->get($this->filepath);

        $this->assertTrue($this->files->exists($this->filepath));
        $this->assertStringContainsString('class ' . $this->classname, $class_content);
    }

    /**
     * Test for creating a new class in subfolder from artisan command.
     *
     * @test
     */
    public function testCreateClassInSubfolder()
    {
        $subfolder = 'Subfolder';
        $filepath = $this->app['path'] . '/Jobs/' . $subfolder . '/' . $this->classname . '.php';

        $this->artisan('laravel-bus:make-job', ['name' => $subfolder . '\\' . $this->classname, '--force' => true]);
        $class_content = $this->files->get($filepath);

        $this->assertTrue($this->files->exists($filepath));
        $this->assertStringContainsString('class ' . $this->classname, $class_content);
        $this->assertStringContainsString('namespace App\\Jobs\\' . $subfolder, $class_content);
    }
}
