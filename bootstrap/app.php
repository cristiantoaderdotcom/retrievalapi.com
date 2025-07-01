<?php

use App\Http\Middleware\PreventSessionForBots;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
	->withRouting(
		web: __DIR__ . '/../routes/web.php',
		api: __DIR__ . '/../routes/api.php',
		commands: __DIR__ . '/../routes/console.php',
		channels: __DIR__.'/../routes/channels.php',
	)
	->withMiddleware(function (Middleware $middleware) {
		$middleware->append([
			PreventSessionForBots::class,
		]);

		$middleware->validateCsrfTokens(except: [
			'stripe/*',
		]);

		$middleware->trustProxies(at: '0.0.0.0/0');
		$middleware->encryptCookies(except: [
			'_fbc',
			'_fbp',
			'_ttp',
		]);

		$middleware->alias([
			'role' => RoleMiddleware::class,
			'permission' => PermissionMiddleware::class,
			'role_or_permission' => RoleOrPermissionMiddleware::class,
		]);
	})
	->withExceptions(function (Exceptions $exceptions) {
		//
	})->create();
