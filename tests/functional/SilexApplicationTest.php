<?php

namespace functional;

use Silex\Application;
use Stack\Stack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SilexApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testWithAppendMiddlewares()
    {
        $app = new Application();

        $app->get('/foo', function () {
            return 'bar';
        });

        $finished = false;

        $app->finish(function () use (&$finished) {
            $finished = true;
        });

        $stack = new Stack();
        $stack->push('functional\Append', '.A');
        $stack->push('functional\Append', '.B');

        $app = $stack->resolve($app);

        $request = Request::create('/foo');
        $response = $app->handle($request);
        $app->terminate($request, $response);

        $this->assertSame('bar.B.A', $response->getContent());
        $this->assertTrue($finished);
    }
}

class Append implements HttpKernelInterface
{
    private $app;
    private $appendix;

    public function __construct(HttpKernelInterface $app, $appendix)
    {
        $this->app = $app;
        $this->appendix = $appendix;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $response = clone $this->app->handle($request, $type, $catch);
        $response->setContent($response->getContent().$this->appendix);

        return $response;
    }
}
