<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CourseCategorizerService;
use App\Services\DataSanitizerService;
use App\Models\ImportBatch;
use App\Models\Lead;

class DataCleanerController extends Controller
{
    protected CourseCategorizerService $categorizer;
    protected DataSanitizerService $sanitizer;

    public function __construct(
        CourseCategorizerService $categorizer,
        DataSanitizerService $sanitizer
    ) {
        $this->categorizer = $categorizer;
        $this->sanitizer = $sanitizer;
    }

    public function index()
    {
        return view('cleaner.index');
    }

    /**
     * Process Dataset Cleaning & Phone-based Deduplication & Categorization
     */
    public function cleanData(Request $request)
    {
        $dataset  = $request->input('dataset', []);
        $mappings = $request->input('mappings', []);
        $customCourseMap = $request->input('customCourseMap', []);
        $dedupeExistingDb = $request->boolean('dedupe_db', true);

        $nameCol   = $mappings['nameCol'] ?? null;
        $emailCol  = $mappings['emailCol'] ?? null;
        $phoneCol  = $mappings['phoneCol'] ?? null;
        $dateCol   = $mappings['dateCol'] ?? null;
        $courseCol = $mappings['courseCol'] ?? null;

        $processed = [];
        $seenPhoneKeys  = [];
        $idCounter = 1;

        // Fetch existing phone numbers in database if requested
        $existingDbPhones = [];
        if ($dedupeExistingDb) {
            $existingDbPhones = Lead::whereNotNull('mob')
                ->where('mob', '!=', '')
                ->pluck('mob')
                ->flip()
                ->toArray();
        }

        $lastValidDate = '';
        $lastValidMonth = '';

        foreach ($dataset as $row) {
            if ($this->sanitizer->isRowEmpty($row)) {
                continue;
            }

            if ($this->sanitizer->shouldExcludeRow($row)) {
                continue;
            }

            $sheetName = $row['_sheet_name'] ?? 'Sheet1';
            $rawName   = $nameCol ? ($row[$nameCol] ?? '') : '';
            $rawEmail  = $emailCol ? ($row[$emailCol] ?? '') : '';
            $rawPhone  = $phoneCol ? ($row[$phoneCol] ?? '') : '';
            $rawDate   = $dateCol ? ($row[$dateCol] ?? '') : '';
            $rawCourse = $courseCol ? ($row[$courseCol] ?? '') : '';

            $cleanedName = $this->sanitizer->cleanName($rawName);
            $emailResult = $this->sanitizer->cleanEmail($rawEmail);
            $phoneResult = $this->sanitizer->cleanPhone($rawPhone);
            $dateResult  = $this->sanitizer->cleanDateAndMonth($rawDate);

            // Forward fill missing dates
            if (!empty($dateResult['date'])) {
                $lastValidDate  = $dateResult['date'];
                $lastValidMonth = $dateResult['month'];
            } else {
                $dateResult['date']  = $lastValidDate;
                $dateResult['month'] = $lastValidMonth;
            }

            $rawCourseStr = trim((string)$rawCourse);
            $category = $this->categorizer->categorize($rawCourseStr, $customCourseMap);

            // Phone Number is the Primary Validation Key for Duplicates
            $phoneKey = $phoneResult['value'];
            $isDuplicate = false;

            if (!empty($phoneKey)) {
                if (isset($seenPhoneKeys[$phoneKey]) || isset($existingDbPhones[$phoneKey])) {
                    $isDuplicate = true;
                } else {
                    $seenPhoneKeys[$phoneKey] = true;
                }
            }

            $processed[] = [
                '_id'             => $idCounter++,
                'sheet_name'      => $sheetName,
                'Date'            => $dateResult['date'],
                'Month'           => $dateResult['month'],
                'Name'            => $cleanedName,
                'Mob'             => $phoneResult['value'], // Plain digits without +
                'Email'           => $emailResult['value'], // Clean email or ''
                'Raw_Course'      => $rawCourseStr ?: '',
                'Major_Category'  => $category,
                'is_duplicate'    => $isDuplicate
            ];
        }

        return response()->json([
            'success'   => true,
            'total'     => count($processed),
            'processed' => $processed
        ]);
    }

    /**
     * Save Cleaned & Deduplicated Leads to Database
     */
    public function saveToDatabase(Request $request)
    {
        $fileName = $request->input('file_name', 'Import_' . date('Ymd_His'));
        $sheetCount = $request->input('sheet_count', 1);
        $leadsData = $request->input('leads', []);
        $skipDuplicates = $request->boolean('skip_duplicates', true);

        if (empty($leadsData)) {
            return response()->json(['error' => 'No leads provided to save.'], 422);
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
            $phone = $lead['Mob'] ?? '';

            // Double check database unique phone constraint
            if (!empty($phone)) {
                $existsInDb = Lead::where('mob', $phone)->exists();
                if ($existsInDb) {
                    $isDup = true;
                }
            }

            if ($isDup) {
                $duplicateCount++;
                if ($skipDuplicates) {
                    continue; // Remove duplicate row by phone validation
                }
            }

            Lead::create([
                'import_batch_id' => $batch->id,
                'sheet_name'      => $lead['sheet_name'] ?? 'Sheet1',
                'date'            => $lead['Date'] ?? '',
                'month'           => $lead['Month'] ?? '',
                'name'            => $lead['Name'] ?? '',
                'mob'             => $phone,
                'email'           => $lead['Email'] ?? '',
                'raw_course'      => $lead['Raw_Course'] ?? '',
                'major_category'  => $lead['Major_Category'] ?? 'Other',
                'is_duplicate'    => $isDup
            ]);

            $insertedCount++;
        }

        $batch->update([
            'unique_count'    => $insertedCount,
            'duplicate_count' => $duplicateCount
        ]);

        return response()->json([
            'success'         => true,
            'message'         => "Successfully saved {$insertedCount} unique leads to Database! ({$duplicateCount} duplicates removed by phone number validation).",
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
        $search   = $request->input('search', '');

        $query = Lead::query()->with('importBatch')->latest();

        if ($category !== 'ALL') {
            $query->where('major_category', $category);
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mob', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('raw_course', 'like', "%{$search}%");
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
        $batch = ImportBatch::findOrFail($id);
        $batch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Batch and associated leads deleted successfully.'
        ]);
    }
}
