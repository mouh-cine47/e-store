<?php

class Geo
{
    public static function getClientIp()
    {
        $candidates = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];

        foreach ($candidates as $key) {
            if (empty($_SERVER[$key])) {
                continue;
            }

            $value = $_SERVER[$key];
            if ($key === 'HTTP_X_FORWARDED_FOR') {
                $parts = explode(',', $value);
                $value = trim($parts[0]);
            }

            if (filter_var($value, FILTER_VALIDATE_IP)) {
                return $value;
            }
        }

        return '0.0.0.0';
    }

    public static function lookup($ip)
    {
        if ($ip === '127.0.0.1' || $ip === '::1' || $ip === '0.0.0.0') {
            return ['city' => 'Local', 'country' => 'Local'];
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return ['city' => 'Local', 'country' => 'Local'];
        }

        $url = 'http://ip-api.com/json/' . rawurlencode($ip) . '?fields=status,country,city';
        $context = stream_context_create([
            'http' => [
                'timeout' => 2,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return ['city' => 'Unknown', 'country' => 'Unknown'];
        }

        $data = json_decode($response, true);
        if (!is_array($data) || ($data['status'] ?? '') !== 'success') {
            return ['city' => 'Unknown', 'country' => 'Unknown'];
        }

        return [
            'city' => $data['city'] ?? 'Unknown',
            'country' => $data['country'] ?? 'Unknown',
        ];
    }
}
