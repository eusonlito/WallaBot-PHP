<?php declare(strict_types=1);

namespace App\Services\Telegram;

use App\Utils\Config;
use App\Utils\Curl;
use App\Utils\Error;
use RuntimeException;
use Throwable;

class Client
{
    private bool $enabled;
    private string $token;
    private ?string $username;
    private ?string $resolvedChatId = null;

    public function __construct()
    {
        $this->enabled = Config::key('private', 'telegram_enabled');
        $this->token = Config::key('private', 'telegram_bot_token');

        try {
            $this->username = strtolower(ltrim(trim(Config::key('private', 'telegram_username')), '@'));
        } catch (RuntimeException) {
            $this->username = null;
        }

        try {
            $this->resolvedChatId = Config::key('private', 'telegram_chat_id');

            if ($this->resolvedChatId === '') {
                $this->resolvedChatId = null;
            }
        } catch (RuntimeException) {
        }
    }

    public function getChatId(): string
    {
        if ($this->resolvedChatId !== null) {
            return (string)$this->resolvedChatId;
        }

        return $this->resolveChatId();
    }

    public function resolveChatId(): string
    {
        if ($this->enabled === false) {
            return '';
        }

        if (empty($this->username)) {
            throw new RuntimeException('Se requiere telegram_username en la configuración para resolver el chat.');
        }

        foreach ($this->getUpdates() as $update) {
            $chat = $update['message']['chat'] ?? null;

            if ($chat && ($chat['type'] === 'private') && (strtolower($chat['username'] ?? '') === $this->username)) {
                return $this->resolvedChatId = (string)$chat['id'];
            }
        }

        throw new RuntimeException("No se pudo encontrar el chat_id para el usuario @{$this->username}. Asegúrate de haberle escrito un mensaje al bot primero.");
    }

    private function getUpdates(): array
    {
        $response = Curl::new()
            ->setMethod('GET')
            ->setUrl($this->url('/getUpdates'))
            ->setJson(true)
            ->send();

        return json_decode($response, true)['result'] ?? [];
    }

    public function sendMessage(string $text): void
    {
        if ($this->enabled === false) {
            return;
        }

        Curl::new()
            ->setMethod('POST')
            ->setUrl($this->url('/sendMessage'))
            ->setJson(true)
            ->setBody($this->sendMessageBody($text))
            ->send();
    }

    private function sendMessageBody(string $text): array
    {
        return [
            'chat_id' => $this->getChatId(),
            'text' => $text,
            'parse_mode' => 'markdown',
        ];
    }

    private function url(string $path): string
    {
        return 'https://api.telegram.org/bot'.$this->token.$path;
    }
}
