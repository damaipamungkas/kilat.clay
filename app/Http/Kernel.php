protected $routeMiddleware = [
    // ... middleware lainnya
    'role' => \App\Http\Middleware\RoleMiddleware::class,
];
protected $middlewareAliases = [
    // ...
    'role' => \App\Http\Middleware\CheckRole::class,
];
