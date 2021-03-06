<?php

namespace Stack;

use Symfony\Component\HttpKernel\HttpKernelInterface;

class Stack
{
    private $specs;

    public function __construct()
    {
        $this->specs = new \SplStack();
    }

    public function push(/*$kernelClass, $args...*/)
    {
        $spec = func_get_args();
        $this->specs->push($spec);

        return $this;
    }

    public function resolve(HttpKernelInterface $app)
    {
        $middlewares = [$app];

        foreach ($this->specs as $spec) {
            $args = $spec;
            $kernelClass = array_shift($args);
            array_unshift($args, $app);

            $reflection = new \ReflectionClass($kernelClass);
            $app = $reflection->newInstanceArgs($args);
            array_unshift($middlewares, $app);
        }

        return new StackedHttpKernel($app, $middlewares);
    }
}
