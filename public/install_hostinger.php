<?php
/**
 * Hostinger One-Click Setup & MySQL Installer Utility
 * F1 Macrotechnologies Data Vault
 */

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$status = [];
$error = null;

try {
    // Step 1: Generate App Key if missing
    if (empty(env('APP_KEY'))) {
        Illuminate\Support\Facades\Artisan::call('key:generate', ['--force' => true]);
        $status[] = "✅ APP_KEY generated successfully.";
    }

    // Step 2: Import MySQL Database Dump
    $dumpFile = __DIR__ . '/../database/f1mtech_data_cleaner_dump.sql';
    if (file_exists($dumpFile)) {
        $sql = file_get_contents($dumpFile);
        Illuminate\Support\Facades\DB::unprepared($sql);
        $count = Illuminate\Support\Facades\DB::table('leads')->count();
        $status[] = "✅ MySQL Database Vault Imported Successfully! Total Leads: {$count}";
    } else {
        $status[] = "⚠️ Dump file database/f1mtech_data_cleaner_dump.sql not found.";
    }

    // Step 3: Clear and Cache Configurations
    Illuminate\Support\Facades\Artisan::call('cache:clear');
    Illuminate\Support\Facades\Artisan::call('view:clear');
    Illuminate\Support\Facades\Artisan::call('config:clear');
    $status[] = "✅ Cache and Views cleared successfully.";

} catch (\Throwable $e) {
    $error = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hostinger Setup - F1 Macrotechnologies</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0f172a; color: #f8fafc; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: #1e293b; border-radius: 16px; padding: 40px; width: 100%; max-width: 600px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5); border: 1px solid #334155; }
        h1 { color: #38bdf8; font-size: 24px; margin-top: 0; text-align: center; }
        .status-box { background: #0f172a; padding: 20px; border-radius: 10px; border: 1px solid #334155; margin: 20px 0; }
        .status-item { margin: 10px 0; font-size: 15px; }
        .error-box { background: #450a0a; color: #fca5a5; padding: 15px; border-radius: 8px; border: 1px solid #7f1d1d; margin-bottom: 20px; font-size: 14px; }
        .btn { display: block; width: 100%; text-align: center; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: white; padding: 14px; border-radius: 8px; text-decoration: none; font-weight: bold; margin-top: 20px; }
        .btn:hover { background: linear-gradient(135deg, #0284c7, #0369a1); }
    </style>
</head>
<body>
    <div class="card">
        <h1>🚀 F1 Macrotechnologies Hostinger Installer</h1>
        <p style="text-align: center; color: #94a3b8; font-size: 14px;">Automated Database Import & Configuration Manager</p>

        <?php if ($error): ?>
            <div class="error-box">
                <strong>Installation Error:</strong><br><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="status-box">
            <?php foreach ($status as $item): ?>
                <div class="status-item"><?= $item ?></div>
            <?php endforeach; ?>
        </div>

        <a href="/" class="btn">✨ Go to F1 Macrotechnologies Portal</a>
    </div>
</body>
</html>
