<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

// Middleware for enabling CORS

// $app->add(new \Tuupola\Middleware\Cors([
//     "origin" => ["*"],
//     "methods" => ["GET", "POST", "PUT", "PATCH", "DELETE"],
//     "headers.allow" => ["Origin", "Accept", "X-Requested-With", "Content-Type", "Access-Control-Request-Method", "Access-Control-Request-Headers", "Authorization"],
//     "headers.expose" => [],
//     "credentials" => false,
//     "cache" => 0,
// ]));


$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Headers', "Access-Control-Allow-Headers, Origin, Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers", "Authorization")
            ->withHeader('Access-Control-Allow-Methods', ' POST, GET, HEAD, PUT, DELETE, OPTIONS');
});
