<?php

class Request {
    private $userData = [];

    public function __construct($userData = []) {
        $this->userData = $userData;
    }

    private function buildHeaders($url, $isPost = false) {

        $headers = [
            'Accept: application/json',
            'Accept-Encoding: gzip',
            'Connection: Keep-Alive',
            'User-Agent: Hepsiburada/5.90.0 (com.pozitron.hepsiburada;build:458;Android 28)OkHttp/5.3.2',
            'X-App-Key: 8093F525-1BAC-49C0-8FB7-C9F7B2DA04CF',
            'X-App-Type: Android',
            'X-App-Version: 5.90.0',
            'X-Language: tr',
            'X-Os-Version: 9',
            'X-Platform: Android'
        ];

        if ($isPost) {
            $headers[] = 'Content-Type: application/json; charset=UTF-8';
        }

        $host = parse_url($url, PHP_URL_HOST);
        $headers[] = 'Host: ' . $host;

        if (!empty($this->userData)) {
            $headers[] = 'Authorization: ' . ($this->userData['authorization'] ?? '');
            $headers[] = 'unique-device-id: ' . ($this->userData['unique_device_id'] ?? '');
            $headers[] = 'X-Anonymous-Id: ' . ($this->userData['x_anonymous_id'] ?? '');
            $headers[] = 'X-Authorization: ' . ($this->userData['x_authorization'] ?? '');
            $headers[] = 'X-Device-Id: ' . ($this->userData['x_device_id'] ?? '');
            $headers[] = 'X-Jwt: ' . ($this->userData['x_jwt'] ?? '');
            $headers[] = 'X-Statsig-Ab-Params: ' . ($this->userData['x_statsig_ab_params'] ?? '');
        }

        return $headers;
    }

    public function get($url) {
        return $this->execute($url, 'GET');
    }

    public function post($url, $bodyArray) {
        return $this->execute($url, 'POST', json_encode($bodyArray));
    }

    public function getUserData() {
        return $this->userData;
    }

private function execute($url, $method, $body = null) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        curl_setopt($ch, CURLOPT_ENCODING, ""); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $headers = $this->buildHeaders($url, ($method === 'POST'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

      return json_encode([
            'code' => $httpCode,
            'data' => json_decode($response, true) ?? $response 
        ], JSON_UNESCAPED_UNICODE);
    }
}
