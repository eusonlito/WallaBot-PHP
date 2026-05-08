<?php declare(strict_types=1);

namespace App\Utils;

use RuntimeException;

class Curl
{
    private string $method = 'GET';
    private string $url;
    private array $headers = [];
    private ?string $body = null;

    public static function new(): self
    {
        return new self();
    }

    public function setMethod(string $method): self
    {
        $this->method = strtoupper($method);
        return $this;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    public function setJson(bool $json): self
    {
        if ($json) {
            $this->headers[] = 'Content-Type: application/json';
            $this->headers[] = 'Accept: application/json';
        }
        return $this;
    }

    public function setBody(array|string $body): self
    {
        $this->body = is_array($body) ? json_encode($body) : $body;
        return $this;
    }

    public function send(): string
    {
        $ch = curl_init($this->url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

        if ($this->body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->body);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = null;

        if (curl_errno($ch)) {
            $error = curl_error($ch);
        }
        
        CurlLogger::log($this->method, $this->url, $this->headers, $this->body, $response !== false ? $response : $error, $httpCode);

        if ($error !== null) {
            throw new RuntimeException('Curl error: '.$error);
        }

        if ($httpCode >= 400) {
            throw new RuntimeException('HTTP error '.$httpCode.': '.$response);
        }

        return (string)$response;
    }
}
