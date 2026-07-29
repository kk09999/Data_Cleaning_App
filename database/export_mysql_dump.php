<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$leads = App\Models\Lead::all();

$sql = "-- F1 Macrotechnologies Data Cleaner MySQL Vault Dump\n";
$sql .= "USE f1mtech_data_cleaner;\n";
$sql .= "TRUNCATE TABLE leads;\n\n";

foreach ($leads as $l) {
    $sheet = addslashes($l->sheet_name ?? 'Sheet1');
    $date = addslashes($l->date ?? '');
    $name = addslashes($l->name ?? '');
    $mob = addslashes($l->mob ?? '');
    $email = addslashes($l->email ?? '');
    $course = addslashes($l->raw_course ?? '');
    $category = addslashes($l->major_category ?? '');
    $source = addslashes($l->source ?? '');
    $status = addslashes($l->status ?? '');

    $sql .= "INSERT INTO leads (sheet_name, date, name, mob, email, raw_course, major_category, source, status, created_at, updated_at) VALUES ('{$sheet}', '{$date}', '{$name}', '{$mob}', '{$email}', '{$course}', '{$category}', '{$source}', '{$status}', NOW(), NOW());\n";
}

file_put_contents(__DIR__ . '/f1mtech_data_cleaner_dump.sql', $sql);
echo "SQL_DUMP_EXPORTED_ROWS: " . count($leads) . "\n";
