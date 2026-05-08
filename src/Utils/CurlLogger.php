<?php declare(strict_types=1);

namespace App\Utils;

class CurlLogger
{
    public static function log(string $method, string $url, array $headers, ?string $requestBody, mixed $responseBody, int $httpCode): void
    {
        $config = Config::get('private');

        if (empty($config['debug'])) {
            return;
        }

        $date = date('Y-m-d');
        $time = date('H:i:s');

        $file = dirname(__DIR__, 2).'/logs/'.$date.'/curl.log';
        Helper::mkdir($file, true);

        $log = "[$time] $method $url - HTTP $httpCode\n";
        $log .= "Headers: ".json_encode($headers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n";

        if ($requestBody !== null) {
            $log .= "Request Body: $requestBody\n";
        }

        $responseStr = is_string($responseBody) ? $responseBody : json_encode($responseBody);

        if (is_string($responseStr) && json_validate($responseStr)) {
            $responseStr = json_encode(json_decode($responseStr, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $log .= "Response:\n".$responseStr."\n";
        $log .= str_repeat('-', 80)."\n";

        file_put_contents($file, $log, FILE_APPEND);
    }
}
