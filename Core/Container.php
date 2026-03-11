<?php

namespace Core;

use Exception;
use ReflectionClass;
use ReflectionNamedType;

class Container
{
    protected $bindings = [];
    protected $singletons = [];

    public function bind($key, $resolver)
    {
        $this->bindings[$key] = $resolver;
    }

    public function singleton($key, $resolver)
    {
        $this->bindings[$key] = function (self $container) use ($key, $resolver) {
            if (!array_key_exists($key, $container->singletons)) {
                $container->singletons[$key] = $container->invokeResolver($resolver);
            }

            return $container->singletons[$key];
        };
    }

    public function resolve($key)
    {
        if (array_key_exists($key, $this->bindings)) {
            $resolver = $this->bindings[$key];
            return $this->invokeResolver($resolver);
        }

        if (class_exists($key)) {
            return $this->build($key);
        }

        throw new Exception("No matching binding found for {$key}");
    }

    private function build(string $className)
    {
        $reflection = new ReflectionClass($className);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $className();
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }

                throw new Exception("Unable to resolve parameter \${$parameter->getName()} for {$className}");
            }

            $dependencies[] = $this->resolve($type->getName());
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    private function invokeResolver(callable $resolver)
    {
        try {
            return $resolver($this);
        } catch (\ArgumentCountError $error) {
            return $resolver();
        }
    }
}
