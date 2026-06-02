<?php

namespace forumaker\YandexSmartCaptcha\SmartCaptcha;

use Flarum\Settings\SettingsRepositoryInterface;
use GuzzleHttp\Client;

class SmartCaptcha
{
    protected Client $client;

    public function __construct(protected SettingsRepositoryInterface $settings)
    {
        $this->client = new Client([
            'base_uri' => 'https://smartcaptcha.cloud.yandex.ru/',
        ]);
    }

    public function verify(string $token, ?string $ip = null): bool
    {
        $secretKey = $this->settings->get('forumaker-yandex-smart-captcha.server_key');

        if (!is_string($secretKey) || $secretKey === '') {
            return false;
        }

        try {
            $params = [
                'secret' => $secretKey,
                'token'  => $token,
            ];

            if ($ip !== null) {
                $params['ip'] = $ip;
            }

            $response = $this->client->request('POST', 'validate', [
                'form_params'     => $params,
                'http_errors'     => false,
                'timeout'         => 10,
                'connect_timeout' => 5,
            ]);

            if ($response->getStatusCode() !== 200) {
                return false;
            }

            $data = json_decode($response->getBody()->getContents(), true);

            return ($data['status'] ?? null) === 'ok';
        } catch (\Throwable $e) {
            return false;
        }
    }
}
