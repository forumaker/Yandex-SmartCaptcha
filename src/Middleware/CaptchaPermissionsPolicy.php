<?php

namespace forumaker\YandexSmartCaptcha\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CaptchaPermissionsPolicy implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $ip = $request->getAttribute('ipAddress')
            ?? $request->getServerParams()['REMOTE_ADDR']
            ?? null;

        if ($ip !== null && !app()->bound('forumaker.captcha.ip')) {
            app()->instance('forumaker.captcha.ip', $ip);
        }

        $response = $handler->handle($request);

        $addition = 'accelerometer=(self "https://captcha.yandex.net")';
        $existing = $response->getHeaderLine('Permissions-Policy');
        $policy   = $existing ? $existing . ', ' . $addition : $addition;

        return $response->withHeader('Permissions-Policy', $policy);
    }
}
