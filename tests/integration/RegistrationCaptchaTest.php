<?php

declare(strict_types=1);

namespace forumaker\YandexSmartCaptcha\Tests\integration;

use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use forumaker\YandexSmartCaptcha\SmartCaptcha\SmartCaptcha;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Psr\Log\NullLogger;
use Psr\Http\Message\ResponseInterface;

/**
 * Sign-up goes through the captcha: with it switched on, a registration needs a "smart-token" that Yandex confirms.
 * Yandex itself is replaced by a canned answer here.
 */
class RegistrationCaptchaTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('forumaker-yandex-smartcaptcha');

        $this->prepareDatabase([
            'users' => [$this->normalUser()],
        ]);
    }

    /** Makes the server key known and has "Yandex" answer with $status. */
    private function yandexAnswers(string $status): void
    {
        $this->setting('forumaker-yandex-smart-captcha.server_key', 'server-secret');
        $this->app();

        $container = $this->app()->getContainer();
        $container->instance(SmartCaptcha::class, new SmartCaptcha(
            $container->make(SettingsRepositoryInterface::class),
            new NullLogger(),
            new Client(['handler' => HandlerStack::create(new MockHandler([new Response(200, [], json_encode(['status' => $status]))]))])
        ));
    }

    /** @param array<string, mixed> $attributes */
    private function register(array $attributes = [], ?int $asUser = null): ResponseInterface
    {
        $options = ['json' => ['data' => ['type' => 'users', 'attributes' => $attributes + [
            'username' => 'newcomer',
            'email' => 'newcomer@example.com',
            'password' => 'a-long-enough-password',
        ]]]];

        if ($asUser !== null) {
            $options['authenticatedAs'] = $asUser;
        }

        return $this->send($this->request('POST', '/api/users', $options)->withAttribute('bypassCsrfToken', true));
    }

    public function test_with_the_captcha_off_registration_needs_no_token(): void
    {
        $this->setting('forumaker-yandex-smart-captcha.signup', '0');

        $this->assertSame(201, $this->register()->getStatusCode());
    }

    public function test_with_the_captcha_on_a_registration_without_a_token_is_refused(): void
    {
        $response = $this->register();

        $this->assertSame(422, $response->getStatusCode(), (string) $response->getBody());
        $this->assertNull($this->database()->table('users')->where('username', 'newcomer')->first());
    }

    public function test_a_token_cannot_pass_when_no_server_key_is_configured(): void
    {
        $this->assertSame(422, $this->register(['smart-token' => 'anything'])->getStatusCode());
    }

    public function test_a_token_yandex_rejects_is_refused(): void
    {
        $this->yandexAnswers('failed');

        $this->assertSame(422, $this->register(['smart-token' => 'forged'])->getStatusCode());
        $this->assertNull($this->database()->table('users')->where('username', 'newcomer')->first());
    }

    public function test_a_token_yandex_confirms_lets_the_registration_through(): void
    {
        $this->yandexAnswers('ok');

        $response = $this->register(['smart-token' => 'genuine']);

        $this->assertSame(201, $response->getStatusCode(), (string) $response->getBody());
        $this->assertNotNull($this->database()->table('users')->where('username', 'newcomer')->first());
    }

    public function test_an_admin_creating_an_account_is_not_asked_for_a_token(): void
    {
        $this->assertSame(201, $this->register([], 1)->getStatusCode());
    }

    public function test_the_permissions_policy_header_allows_the_captcha_widget(): void
    {
        $response = $this->send($this->request('GET', '/api'));

        $this->assertStringContainsString('accelerometer=(self "https://captcha.yandex.net")', $response->getHeaderLine('Permissions-Policy'));
    }
}
