<?php declare(strict_types=1);

namespace App\Controllers;

use Exception;
use App\Utils\Config;
use App\Utils\Error;

abstract class ControllerAbstract
{
    protected array $input;

    abstract public function handle(): void;

    public function __construct()
    {
        $this->setInput();
    }

    protected function setInput(): self
    {
        $raw = file_get_contents('php://input');

        if (json_validate($raw)) {
            $input = json_decode($raw, true);
        } else {
            $input = [];
        }

        $this->input = $input + $_POST + $_GET;

        return $this;
    }

    protected function getInput(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->input) ? $this->input[$key] : $default;
    }

    protected function getInputString(string $key, mixed $default = null): ?string
    {
        return is_string($value = $this->getInput($key)) ? $value : $default;
    }

    protected function getInputInt(string $key, mixed $default = null): ?int
    {
        $value = $this->getInput($key);

        if (is_integer($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^[0-9]+$/', $value)) {
            return intval($value);
        }

        return $default;
    }

    protected function getInputBool(string $key, ?bool $default = null): ?bool
    {
        return filter_var($this->input[$key] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    protected function getInputFloat(string $key, mixed $default = null): ?float
    {
        $value = $this->getInput($key);

        if (is_numeric($value)) {
            return floatval($value);
        }

        return $default;
    }

    public function middlewareAuthBasic(): self
    {
        $user = Config::key('private', 'auth_user');
        $password = Config::key('private', 'auth_password');

        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(60 * 60 * 24 * 365);
            session_start();
        }

        if (($_SESSION['authenticated'] ?? false) === true) {
            return $this;
        }

        if ($this->middlewareAuthBasicIsValid($user, $password)) {
            $_SESSION['authenticated'] = true;
            return $this;
        }

        header('WWW-Authenticate: Basic realm="WallaBot"');

        http_response_code(401);

        die('401 Unauthorized');
    }

    private function middlewareAuthBasicIsValid(string $user, string $password): bool
    {
        return $user && $password && ((($_SERVER['PHP_AUTH_USER'] ?? '') === $user) && (($_SERVER['PHP_AUTH_PW'] ?? '') === $password));
    }

    protected function response(mixed $data, int $status = 200): void
    {
        if (($status !== 200) && is_array($data) && isset($data['error'])) {
            Error::report(new Exception($data['error']));
        }

        http_response_code($status);
        header('Content-Type: application/json');

        if (is_object($data) && method_exists($data, 'toArray')) {
            $data = $data->toArray();
        }

        if (is_array($data)) {
            $data = json_encode($data, JSON_THROW_ON_ERROR);
        }

        die(strval($data));
    }

    protected function redirect(string $url): void
    {
        header('Location: '.$url);
        exit;
    }
}
