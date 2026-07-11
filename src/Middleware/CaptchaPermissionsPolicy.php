<?php

namespace forumaker\YandexSmartCaptcha\Middleware;

use Illuminate\Contracts\Container\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CaptchaPermissionsPolicy implements MiddlewareInterface
{
    public function __construct(
        protected Container $container
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $ip = $request->getAttribute('ipAddress')
            ?? $request->getServerParams()['REMOTE_ADDR']
            ?? null;

        // Always rebind (never guard with `bound()`): in long-running
        // process environments (Octane, RoadRunner) the container survives
        // across requests, so a one-time bind would leak the first
        // request's IP into every later captcha verification.
        if ($ip !== null) {
            $this->container->instance('forumaker.captcha.ip', $ip);
        }

        $response = $handler->handle($request);

        $addition = 'accelerometer=(self "https://captcha.yandex.net")';
        $existing = $response->getHeaderLine('Permissions-Policy');
        $policy   = $existing ? $existing . ', ' . $addition : $addition;

        return $response->withHeader('Permissions-Policy', $policy);
    }
}
