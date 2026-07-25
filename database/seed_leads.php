<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Lead;
use App\Models\ImportBatch;

DB::statement('SET FOREIGN_KEY_CHECKS=0;');
Lead::truncate();
ImportBatch::truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

if (true) {
    $batch = ImportBatch::create([
        'file_name' => 'Initial_Master_Leads.xlsx',
        'sheet_count' => 1,
        'record_count' => 8,
        'unique_count' => 8,
        'duplicate_count' => 0
    ]);

    $leads = [
        [
            'import_batch_id' => $batch->id,
            'sheet_name' => 'Sheet1',
            'date' => '2024-03-15',
            'month' => 'March',
            'year' => '2024',
            'quarter' => 'Q1',
            'name' => 'Rahul Sharma',
            'mob' => '9818845002',
            'email' => 'rahul.sharma@gmail.com',
            'raw_course' => 'AI',
            'major_category' => 'Data Analyst and Scientist',
            'source' => 'Google Ads',
            'status' => 'Enrolled',
            'notes' => 'Enrolled in Data Analyst course',
            'is_duplicate' => false
        ],
        [
            'import_batch_id' => $batch->id,
            'sheet_name' => 'Sheet1',
            'date' => '2024-05-10',
            'month' => 'May',
            'year' => '2024',
            'quarter' => 'Q2',
            'name' => 'Priya Verma',
            'mob' => '9990349899',
            'email' => 'priya.v@gmail.com',
            'raw_course' => 'DA+CGAI',
            'major_category' => 'Data Analyst and Scientist',
            'source' => 'Facebook',
            'status' => 'Visited',
            'notes' => 'Visited campus on May 10',
            'is_duplicate' => false
        ],
        [
            'import_batch_id' => $batch->id,
            'sheet_name' => 'Sheet1',
            'date' => '2024-07-22',
            'month' => 'July',
            'year' => '2024',
            'quarter' => 'Q3',
            'name' => 'Amit Kumar',
            'mob' => '9811223344',
            'email' => 'amit.k@gmail.com',
            'raw_course' => 'MSO+M-DA',
            'major_category' => 'Data Analyst and Scientist',
            'source' => 'Instagram',
            'status' => 'Fresh Lead',
            'notes' => 'Requested syllabus PDF',
            'is_duplicate' => false
        ],
        [
            'import_batch_id' => $batch->id,
            'sheet_name' => 'Sheet1',
            'date' => '2024-08-14',
            'month' => 'August',
            'year' => '2024',
            'quarter' => 'Q3',
            'name' => 'Sneha Gupta',
            'mob' => '8800112233',
            'email' => 'sneha.gupta@gmail.com',
            'raw_course' => 'Tally GST',
            'major_category' => 'Accounting and Taxation',
            'source' => 'Website Direct',
            'status' => 'Enrolled',
            'notes' => 'Enrolled in weekend batch',
            'is_duplicate' => false
        ],
        [
            'import_batch_id' => $batch->id,
            'sheet_name' => 'Sheet1',
            'date' => '2024-09-05',
            'month' => 'September',
            'year' => '2024',
            'quarter' => 'Q3',
            'name' => 'Vikas Singh',
            'mob' => '9711554433',
            'email' => 'vikas.singh@gmail.com',
            'raw_course' => 'Full Stack MERN',
            'major_category' => 'Full Stack Developer',
            'source' => 'LinkedIn',
            'status' => 'Interested',
            'notes' => 'Attended online demo class',
            'is_duplicate' => false
        ],
        [
            'import_batch_id' => $batch->id,
            'sheet_name' => 'Sheet1',
            'date' => '2024-10-12',
            'month' => 'October',
            'year' => '2024',
            'quarter' => 'Q4',
            'name' => 'Neha Rani',
            'mob' => '9899001122',
            'email' => 'neha.rani@gmail.com',
            'raw_course' => 'Python Data Science',
            'major_category' => 'Data Analyst and Scientist',
            'source' => 'WhatsApp',
            'status' => 'Enrolled',
            'notes' => 'Enrolled in fast-track program',
            'is_duplicate' => false
        ],
        [
            'import_batch_id' => $batch->id,
            'sheet_name' => 'Sheet1',
            'date' => '2024-11-20',
            'month' => 'November',
            'year' => '2024',
            'quarter' => 'Q4',
            'name' => 'Anil Kapoor',
            'mob' => '9810998877',
            'email' => 'anil.kapoor@gmail.com',
            'raw_course' => 'Excel VBA',
            'major_category' => 'Data Analyst and Scientist',
            'source' => 'Referral',
            'status' => 'No Need to Call',
            'notes' => 'Student data / not interested',
            'is_duplicate' => false
        ],
        [
            'import_batch_id' => $batch->id,
            'sheet_name' => 'Sheet1',
            'date' => '2024-12-01',
            'month' => 'December',
            'year' => '2024',
            'quarter' => 'Q4',
            'name' => 'Kavita Roy',
            'mob' => '9717665544',
            'email' => 'kavita.roy@gmail.com',
            'raw_course' => 'Power BI',
            'major_category' => 'Data Analyst and Scientist',
            'source' => 'Meta Ads',
            'status' => 'Visited',
            'notes' => 'Visited campus with parents',
            'is_duplicate' => false
        ]
    ];

    foreach ($leads as $l) {
        Lead::create($l);
    }
    echo "SUCCESSFULLY SEEDED " . count($leads) . " LEADS INTO MYSQL DATABASE!\n";
} else {
    echo "DATABASE ALREADY CONTAINS LEADS: " . Lead::count() . "\n";
}
