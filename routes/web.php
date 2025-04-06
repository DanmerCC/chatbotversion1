<?php
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $routes = Route::getRoutes();
    $routeList = [];

    $ignoredPatterns = [
        '#^sanctum(/.*)?$#',
        '#^api/user$#',
        '#^_ignition(/.*)?$#',
        '#^telescope(/.*)?$#',
        '#^_debugbar(/.*)?$#',
    ];

    foreach ($routes as $route) {
        $uri = $route->uri();

        foreach ($ignoredPatterns as $pattern) {
            if (preg_match($pattern, $uri)) {
                continue 2;
            }
        }

        $action = $route->getAction();

        $routeList[] = [
            'method' => implode('|', $route->methods()),
            'uri' => $uri,
            'name' => $route->getName(),
            'middleware' => implode(', ', $route->gatherMiddleware()),
            'controller' => $action['controller'] ?? '',
            'description' => $route->defaults['description'] ?? '',
        ];
    }

    return view('docs.routes', ['routes' => $routeList]);
});

