<?php declare(strict_types=1);

namespace App\Controllers;

use App\Utils\Router;

$router = new Router();

$router->get('/', static fn () => new PageResults()->middlewareAuthBasic()->handle());
$router->get('/searches', static fn () => new PageSearches()->middlewareAuthBasic()->handle());

$router->post('/searches/save', static fn () => new SearchSave()->middlewareAuthBasic()->handle());
$router->post('/searches/delete', static fn () => new SearchDelete()->middlewareAuthBasic()->handle());
$router->post('/items/favorite', static fn () => new ItemFavorite()->middlewareAuthBasic()->handle());
$router->post('/items/hide', static fn () => new ItemHide()->middlewareAuthBasic()->handle());

$router->get('/logout', static fn () => new AuthLogout()->handle());

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
