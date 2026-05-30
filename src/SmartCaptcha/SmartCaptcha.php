<?php

namespace forumaker\YandexSmartCaptcha\SmartCaptcha;

use GuzzleHttp\Client;

class SmartCaptcha
{
    protected string $secretKey;
    protected Client $client;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;

        $this->client = new Client([
            'base_uri' => 'https://smartcaptcha.cloud.yandex.ru/',
        ]);
    }

    public function verify(string $token, ?string $ip = null): bool
    {
        try {
            $params = [
                'secret' => $this->secretKey,
                'token'  => $token,
            ];

            if ($ip !== null) {
                $params['ip'] = $ip;
            }

            $response = $this->client->request('POST', 'validate', [
                'form_params' => $params,
                'http_errors' => false,
                'timeout' => 10,
                'connect_timeout' => 5,
            ]);

            if ($response->getStatusCode() !== 200) {
                return false;
            }

            $data = json_decode($response->getBody()->getContents(), true);

            return ($data['status'] ?? null) === 'ok';
        } catch (\Throwable $e) {
            return true;
        }
    }
}