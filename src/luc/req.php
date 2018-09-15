<?php

namespace luc;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\{
    RequestException,
    BadResponseException
};

class req
{
    public static $config = [
        'base_uri'  => null,
        'timeout'   => 5,
    ];

    public static $silent = true;

    public static $decoder = null;

    public static function __callStatic(string $method, array $arguments) {
        $method = strtoupper($method);
        $path   = array_shift($arguments);
        $extra  = [];
        if (isset($arguments[0]) && is_array($arguments[0])) {
            $params = array_shift($arguments);
            if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
                $extra['query'] = $params;
            } else {
                $extra['form_params'] = $params;
            }
        }
        $name = null;
        foreach ($arguments as $value) {
            if ($name) {
                $extra[$name] = $value;
                $name = null;
            } else {
                $name = $value;
            }
        }

        $client     = new Client(static::$config);
        try {
            $response   = $client->request($method, $path, $extra);
        } catch (RequestException $exc) {
            if (static::$silent) {
                $response = $exc->getResponse();
            } else {
                throw $exc;
            }
        }
        if (!$response) {
            throw new \RuntimeException("Request timeout.");
        }

        return [
            static::decode($response->getBody()),
            $response->getStatusCode(),
            $response->getHeaders(),
        ];
    }

    private static function decode(?string $body) {
        if (!$body) {
            return null;
        }

        $decoder = static::$decoder ?: function($data) {
            return \json_decode($data, true) ?: $data;
        };

        return $decoder($body);
    }
}