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

    public function verify(string $token): bool
    {
        $response = $this->client->request('POST', 'validate', [
            'form_params' => [
                'secret' => $this->secretKey,
                'token'  => $token,
            ],
            'http_errors' => false,
            'timeout' => 5,
            'connect_timeout' => 5,
        ]);

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $data = json_decode($response->getBody()->getContents(), true);

        return ($data['status'] ?? null) === 'ok';
    }
}