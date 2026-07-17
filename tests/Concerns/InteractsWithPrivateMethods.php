<?php

namespace Tests\Concerns;

trait InteractsWithPrivateMethods
{
    /**
     * Call a private/protected method on an object and return its result.
     */
    protected function callPrivateMethod(object $object, string $method, array $args = []): mixed
    {
        $reflection = new \ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $args);
    }
}
