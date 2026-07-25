<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\ImportBatch;
use App\Services\CourseCategorizerService;
use App\Services\DataSanitizerService;

class DataCleanerController extends Controller
{
    protected $categorizer;
    protected $sanitizer;

    public function __construct(
        CourseCategorizerService $categorizer,
        DataSanitizerService $sanitizer
    ) {
        $this->categorizer = $categorizer;
        $this->sanitizer = $sanitizer;
    }

    /**
     * Render the Master Data Cleaner Page
     */
    public function index()
    {
        $dbLeadsCount = Lead::count();
        return view('cleaner.index', compact('dbLeadsCount'));
    }

    /**
     * Process Raw Lead Dataset (JSON array or raw input)
     */
    public function cleanData(Request $request)
    {
        $rawRows = $request->input('data', []);
        $mappings = $request->input('mappings', []);

        if (empty($rawRows) || !is_array($rawRows)) {
            return response()->json([
                'success' => false,
                'error' => 'No dataset provided'
            ], 400);
        }

        $dateCol   = $mappings['dateCol'] ?? null;
        $nameCol   = $mappings['nameCol'] ?? null;
        $phoneCol  = $mappings['phoneCol'] ?? null;
        $emailCol  = $mappings['emailCol'] ?? null;
        $courseCol = $mappings['courseCol'] ?? null;
        $sourceCol = $mappings['sourceCol'] ?? null;

        $processed = [];
        $seenPhones = [];
        $lastValidDate = '';
        $lastValidMonth = '';

        // Query existing phones in database for deduplication
        $existingDbPhones = Lead::whereNotNull('mob')->where('mob', '!=', '')->pluck('mob')->toArray();
        $dbPhoneSet = array_flip($existingDbPhones);

        $counter = 1;
        foreach ($rawRows as $row) {
            if ($this->sanitizer->isRowEmpty($row)) {
                continue;
            }

            if ($this->sanitizer->shouldExcludeRow($row)) {
                continue;
            }

            $rawDate   = $dateCol ? ($row[$dateCol] ?? '') : '';
            $rawName   = $nameCol ? ($row[$nameCol] ?? '') : '';
            $rawPhone  = $phoneCol ? ($row[$phoneCol] ?? '') : '';
            $rawEmail  = $emailCol ? ($row[$emailCol] ?? '') : '';
            $rawCourse = $courseCol ? ($row[$courseCol] ?? '') : '';
            $rawSource = $sourceCol ? ($row[$sourceCol] ?? '') : 'Direct/Organic';

            $name  = $this->sanitizer->cleanName($rawName);
            $phone = $this->sanitizer->cleanPhone($rawPhone);
            $email = $this->sanitizer->cleanEmail($rawEmail);
            $source = !empty($rawSource) ? trim($rawSource) : 'Direct/Organic';

            $dateInfo = $this->sanitizer->parseRealDateAndMonth($rawDate);
            if (!empty($dateInfo['date'])) {
                $lastValidDate  = $dateInfo['date'];
                $lastValidMonth = $dateInfo['month'] ?: $lastValidMonth;
            } else {
                $dateInfo['date']  = $lastValidDate;
                $dateInfo['month'] = $lastValidMonth;
            }

            $course   = trim((string)$rawCourse);
            $category = $this->categorizer->categorize($course);

            $isDuplicate = false;
            if (!empty($phone)) {
                if (isset($seenPhones[$phone]) || isset($dbPhoneSet[$phone])) {
                    $isDuplicate = true;
                } else {
                    $seenPhones[$phone] = true;
                }
            }

            $processed[] = [
                '_id'            => $counter++,
                'sheet_name'     => $row['_sheet_name'] ?? 'Sheet1',
                'Date'           => $dateInfo['date'],
                'Month'          => $dateInfo['month'],
                'Name'           => $name,
                'Mob'            => $phone,
                'Email'          => $email,
                'Raw_Course'     => $course,
                'Major_Category' => $category,
                'Source'         => $source,
                'is_duplicate'   => $isDuplicate
            ];
        }

        return response()->json([
            'success' => true,
            'total'   => count($processed),
            'unique'  => count(array_filter($processed, fn($r) => !$r['is_duplicate'])),
            'data'    => $processed
        ]);
    }

    /**
     * Save Clean Unique Leads to SQLite/MySQL Database
     */
    public function saveToDatabase(Request $request)
    {
        $fileName       = $request->input('file_name', 'Imported_Workbook.xlsx');
        $sheetCount     = $request->input('sheet_count', 1);
        $leadsData      = $request->input('leads', []);
        $skipDuplicates = $request->boolean('skip_duplicates', true);

        if (empty($leadsData)) {
            return response()->json(['success' => false, 'error' => 'No lead data to save.'], 400);
        }

        $insertedCount  = 0;
        $duplicateCount = 0;

        $batch = ImportBatch::create([
            'file_name'       => $fileName,
            'sheet_count'     => $sheetCount,
            'record_count'    => count($leadsData),
            'unique_count'    => 0,
            'duplicate_count' => 0
        ]);

        foreach ($leadsData as $lead) {
            $isDup = $lead['is_duplicate'] ?? false;
            $phone = $lead['Mob'] ?? ($lead['mob'] ?? '');

            // Strict Database Deduplication: Reject any phone number already in DB
            if (!empty($phone)) {
                $existsInDb = Lead::where('mob', $phone)->exists();
                if ($existsInDb) {
                    $duplicateCount++;
                    continue; // Strictly skip duplicate lead from inserting
                }
            }

            if ($isDup && $skipDuplicates) {
                $duplicateCount++;
                continue;
            }

            Lead::create([
                'import_batch_id' => $batch->id,
                'sheet_name'      => $lead['sheet_name'] ?? 'Sheet1',
                'date'            => $lead['Date'] ?? ($lead['date'] ?? ''),
                'month'           => $lead['Month'] ?? ($lead['month'] ?? ''),
                'name'            => $lead['Name'] ?? ($lead['name'] ?? ''),
                'mob'             => $phone,
                'email'           => $lead['Email'] ?? ($lead['email'] ?? ''),
                'raw_course'      => $lead['Raw_Course'] ?? ($lead['raw_course'] ?? ''),
                'major_category'  => $lead['Major_Category'] ?? ($lead['major_category'] ?? 'Other'),
                'source'          => $lead['Source'] ?? ($lead['source'] ?? 'Direct/Organic'),
                'is_duplicate'    => false
            ]);

            $insertedCount++;
        }

        $batch->update([
            'unique_count'    => $insertedCount,
            'duplicate_count' => $duplicateCount
        ]);

        return response()->json([
            'success'         => true,
            'message'         => "Successfully saved {$insertedCount} new unique leads to Database! ({$duplicateCount} duplicate records skipped).",
            'batch_id'        => $batch->id,
            'saved_count'     => $insertedCount,
            'duplicate_count' => $duplicateCount
        ]);
    }

    /**
     * Fetch Stored Leads from Database
     */
    public function getDatabaseLeads(Request $request)
    {
        $category = $request->input('category', 'ALL');
        $source   = $request->input('source', 'ALL');
        $search   = $request->input('search', '');

        $query = Lead::query()->with('importBatch')->latest();

        if ($category !== 'ALL') {
            $query->where('major_category', $category);
        }

        if ($source !== 'ALL') {
            $query->where('source', $source);
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mob', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('raw_course', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%");
            });
        }

        $leads = $query->get();
        $batches = ImportBatch::latest()->get();

        return response()->json([
            'success' => true,
            'total'   => Lead::count(),
            'batches' => $batches,
            'leads'   => $leads
        ]);
    }

    /**
     * Delete a Batch and its associated Leads
     */
    public function deleteBatch($id)
    {
        $batch = ImportBatch::find($id);
        if (!$batch) {
            return response()->json(['success' => false, 'error' => 'Batch not found'], 404);
        }

        $batch->delete(); // Cascade deletes associated leads

        return response()->json([
            'success' => true,
            'message' => 'Import batch deleted successfully.'
        ]);
    }
}
