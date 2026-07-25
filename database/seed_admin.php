<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\User::updateOrCreate(
    ['email' => 'admin@f1mtech.com'],
    [
        'name' => 'F1 MTech Admin',
        'password' => \Illuminate\Support\Facades\Hash::make('admin123')
    ]
);

echo "ADMIN USER CREATED SUCCESSFULLY IN MYSQL DATABASE!\n";
