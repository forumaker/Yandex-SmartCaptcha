<?php

declare(strict_types=1);

namespace forumaker\YandexSmartCaptcha\Tests\unit;

use Flarum\Settings\SettingsRepositoryInterface;
use forumaker\YandexSmartCaptcha\SmartCaptcha\SmartCaptcha;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

/**
 * The server-side check against Yandex: only an explicit {"status": "ok"} lets a request through; everything else —
 * a missing key, a rejected token, an HTTP error, a dead network — is a "no".
 */
class SmartCaptchaTest extends TestCase
{
    /** @var array<int, array{request: \Psr\Http\Message\RequestInterface}> */
    private array $history = [];

    private function settings(?string $serverKey): SettingsRepositoryInterface
    {
        return new class($serverKey) implements SettingsRepositoryInterface {
            public function __construct(private ?string $serverKey)
            {
            }

            public function all(): array
            {
                return [];
            }

            public function get(string $key, mixed $default = null): mixed
            {
                return $key === 'forumaker-yandex-smart-captcha.server_key' ? $this->serverKey : $default;
            }

            public function set(string $key, mixed $value): void
            {
            }

            public function delete(string $keyLike): void
            {
            }
        };
    }

    private function logger(): AbstractLogger
    {
        return new class extends AbstractLogger {
            /** @var array<int, array{level: string, message: string}> */
            public array $records = [];

            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $this->records[] = ['level' => (string) $level, 'message' => (string) $message];
            }
        };
    }

    /** @param array<int, Response|\Throwable> $queue */
    private function captcha(?string $serverKey, array $queue, ?AbstractLogger $logger = null): SmartCaptcha
    {
        $this->history = [];
        $stack = HandlerStack::create(new MockHandler($queue));
        $stack->push(Middleware::history($this->history));

        return new SmartCaptcha(
            $this->settings($serverKey),
            $logger ?? $this->logger(),
            new Client(['handler' => $stack, 'base_uri' => 'https://smartcaptcha.cloud.yandex.ru/'])
        );
    }

    private function ok(string $status = 'ok'): Response
    {
        return new Response(200, [], json_encode(['status' => $status, 'host' => 'questpost.ru']));
    }

    public function test_an_ok_answer_passes(): void
    {
        $this->assertTrue($this->captcha('secret', [$this->ok()])->verify('token-1'));
    }

    public function test_the_token_and_secret_are_sent_to_the_validate_endpoint(): void
    {
        $this->captcha('secret', [$this->ok()])->verify('token-1');

        $request = $this->history[0]['request'];

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/validate', $request->getUri()->getPath());
        parse_str((string) $request->getBody(), $form);
        $this->assertSame(['secret' => 'secret', 'token' => 'token-1'], $form);
    }

    public function test_the_visitors_address_is_sent_when_known(): void
    {
        $this->captcha('secret', [$this->ok()])->verify('token-1', '203.0.113.7');

        parse_str((string) $this->history[0]['request']->getBody(), $form);
        $this->assertSame('203.0.113.7', $form['ip']);
    }

    public function test_a_rejected_token_fails(): void
    {
        $this->assertFalse($this->captcha('secret', [$this->ok('failed')])->verify('token-1'));
    }

    public function test_a_body_without_a_status_fails(): void
    {
        $this->assertFalse($this->captcha('secret', [new Response(200, [], '{}')])->verify('token-1'));
        $this->assertFalse($this->captcha('secret', [new Response(200, [], 'not json')])->verify('token-1'));
    }

    public function test_an_http_error_fails_and_is_logged(): void
    {
        $logger = $this->logger();

        $this->assertFalse($this->captcha('secret', [new Response(500)], $logger)->verify('token-1'));
        $this->assertSame('error', $logger->records[0]['level']);
    }

    public function test_a_network_failure_fails_closed_instead_of_throwing(): void
    {
        $logger = $this->logger();
        $captcha = $this->captcha(
            'secret',
            [new \GuzzleHttp\Exception\ConnectException('timeout', new \GuzzleHttp\Psr7\Request('POST', 'validate'))],
            $logger
        );

        $this->assertFalse($captcha->verify('token-1'));
        $this->assertSame('error', $logger->records[0]['level']);
    }

    public function test_without_a_server_key_nothing_is_ever_valid_and_yandex_is_not_called(): void
    {
        foreach ([null, ''] as $key) {
            $this->assertFalse($this->captcha($key, [$this->ok()])->verify('token-1'));
            $this->assertCount(0, $this->history);
        }
    }
}
