<?php

namespace Nedwors\Hopper\Traits\Console;

use Exception;
use Illuminate\Support\Str;
use Nedwors\Hopper\Exceptions\NoConnectionException;

trait CatchesExceptions
{
    private static $exceptions = [
        NoConnectionException::class
    ];

    public function tryTo(callable $callable)
    {
        try {
            return $callable();
        } catch (Exception $e) {
            if (!in_array(get_class($e), static::$exceptions)) {
                throw $e;
            }

            $this->handleException($e);
        }
    }

    protected function handleException($exception)
    {
        $exceptionMethod = Str::of(get_class($exception))->afterLast("\\")->camel()->__toString();
        $exceptionMethodMessage = $exceptionMethod . "Message";

        $this->{$exceptionMethod}($exception, fn() => $this->{$exceptionMethodMessage}($exception));
    }

    protected function noConnectionException(NoConnectionException $exception, $message)
    {
        $message();
    }

    protected function noConnectionExceptionMessage(NoConnectionException $exception)
    {
        return $this->warn("Sorry, your database connection is not currently supported by Hopper");
    }
}
