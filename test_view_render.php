<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);

$response = $kernel->handle(Illuminate\Http\Request::create('/', 'GET'));
echo "DASHBOARD RENDER STATUS: " . $response->getStatusCode() . "\n";
if ($response->exception) {
    echo "EX: " . $response->exception->getMessage() . "\n";
}

unlink(__FILE__);
