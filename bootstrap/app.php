<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'assistant.access' => \App\Http\Middleware\CheckAssistantAccess::class,
            'module' => \App\Http\Middleware\CheckModuleEnabled::class,
            'module.permission' => \App\Http\Middleware\CheckModulePermission::class,
            'smart_serial.access' => \App\Http\Middleware\EnsureSmartSerialAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
