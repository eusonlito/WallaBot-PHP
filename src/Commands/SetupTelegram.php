<?php declare(strict_types=1);

require __DIR__.'/../autoload.php';

use App\Services\Telegram\Client;
use App\Utils\Console;

Console::info('Resolviendo Chat ID desde Telegram...');

try {
    $chatId = new Client()->resolveChatId();
    $file = dirname(__DIR__, 2).'/config/private.local.json';

    if (is_file($file)) {
        $data = json_decode(file_get_contents($file), true) ?: [];
    } else {
        $data = [];
    }

    $data['telegram_chat_id'] = $chatId;

    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

    Console::success("¡Éxito! El Chat ID ({$chatId}) ha sido guardado en config/private.local.json");
} catch (\Throwable $e) {
    Console::error("Error: ".$e->getMessage());
}
