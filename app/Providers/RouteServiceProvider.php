<?php

namespace App\Providers;

/**
 * Kompatibilitas dengan controller auth klasik (port app lama).
 * Routing modern didefinisikan di routes/*.php via bootstrap/app.php.
 */
class RouteServiceProvider
{
    public const HOME = '/dashboard';
}
