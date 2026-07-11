<?php

namespace forumaker\YandexSmartCaptcha\SmartCaptcha;

use Flarum\Settings\SettingsRepositoryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

class SmartCaptcha
{
    protected ClientInterface $client;

    public function __construct(
        protected SettingsRepositoryInterface $settings,
        protected LoggerInterface $logger,
        ?ClientInterface $client = null
    ) {
        $this->client = $client ?? new Client([
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
                $this->logger->error('[YandexSmartCaptcha] Unexpected HTTP status from validate endpoint', [
                    'status' => $response->getStatusCode(),
                ]);

                return false;
            }

            $data = json_decode($response->getBody()->getContents(), true);
            $status = $data['status'] ?? null;

            if ($status !== 'ok') {
                $this->logger->info('[YandexSmartCaptcha] Token rejected by Yandex', [
                    'status'  => $status,
                    'message' => $data['message'] ?? null,
                ]);
            }

            return $status === 'ok';
        } catch (\Throwable $e) {
            $this->logger->error('[YandexSmartCaptcha] Verification request failed', [
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
