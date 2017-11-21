<?php

namespace Tests;

use Mockery;
use Exception;
use App\Exceptions\Handler;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Contracts\Debug\ExceptionHandler;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use CreatesApplication;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    protected function setUp()
    {
        parent::setUp();
        Mockery::getConfiguration()->allowMockingNonExistentMethods(false);

        TestResponse::macro('data', function ($key) {
            return $this->original->getData()[$key];
        });
    }

    protected function disableExceptionHandling()
    {

        // Disable Laravel's default exception handling  
        // and allow exceptions to bubble up the stack  
        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct() {}
            public function report(Exception $exception) {}
            public function render($request, Exception $exception)
            {
                throw $exception;
            }

        });
    }
}
