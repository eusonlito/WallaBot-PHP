<?php declare(strict_types=1);

namespace App\Controllers;

class AuthLogout extends ControllerAbstract
{
    public function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_destroy();
        
        header('HTTP/1.0 401 Unauthorized');
        
        $page = 'logout';
        require __DIR__ . '/../Views/layout.php';
        exit;
    }
}
