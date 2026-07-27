<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\ImportBatch;
use App\Services\CourseCategorizerService;
use App\Services\DataSanitizerService;
use Illuminate\Support\Facades\DB;

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
        $lastValidYear = '';
        $lastValidQuarter = '';

        // Query existing phones in database for deduplication
        $existingDbPhones = Lead::whereNotNull('mob')->where('mob', '!=', '')->pluck('mob')->toArray();
        $dbPhoneSet = array_flip($existingDbPhones);

        $counter = 1;
        foreach ($rawRows as $row) {
            if ($this->sanitizer->isRowEmpty($row)) {
                continue;
            }

            $rawDate   = $dateCol ? ($row[$dateCol] ?? '') : '';
            $rawName   = $nameCol ? ($row[$nameCol] ?? '') : '';
            $rawPhone  = $phoneCol ? ($row[$phoneCol] ?? '') : '';
            $rawEmail  = $emailCol ? ($row[$emailCol] ?? '') : '';
            $rawCourse = $courseCol ? ($row[$courseCol] ?? '') : '';
            $rawSource = $sourceCol ? ($row[$sourceCol] ?? '') : 'Direct/Organic';

            $name  = $this->sanitizer->cleanName($rawName);
            $phoneArr = $this->sanitizer->cleanPhone($rawPhone);
            $phone = $phoneArr['value'] ?? '';

            $emailArr = $this->sanitizer->cleanEmail($rawEmail);
            $email = $emailArr['value'] ?? '';

            $source = !empty($rawSource) ? trim((string)$rawSource) : 'Direct/Organic';
            $status = $this->sanitizer->detectStatus($row);

            $dateInfo = $this->sanitizer->cleanDateAndMonth($rawDate);
            if (!empty($dateInfo['date'])) {
                $lastValidDate    = $dateInfo['date'];
                $lastValidMonth   = $dateInfo['month'] ?: $lastValidMonth;
                $lastValidYear    = $dateInfo['year'] ?: $lastValidYear;
                $lastValidQuarter = $dateInfo['quarter'] ?: $lastValidQuarter;
            } else {
                $dateInfo['date']    = $lastValidDate;
                $dateInfo['month']   = $lastValidMonth;
                $dateInfo['year']    = $lastValidYear;
                $dateInfo['quarter'] = $lastValidQuarter;
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
                'Year'           => $dateInfo['year'] ?: (date('Y')),
                'Quarter'        => $dateInfo['quarter'] ?: 'Q1',
                'Name'           => $name,
                'Mob'            => $phone,
                'Email'          => $email,
                'Raw_Course'     => $course,
                'Major_Category' => $category,
                'Source'         => $source,
                'Status'         => $status,
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
     * Save Clean Unique Leads to Database
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
        $invalidCount   = 0;

        $batch = ImportBatch::create([
            'file_name'       => $fileName,
            'sheet_count'     => $sheetCount,
            'record_count'    => count($leadsData),
            'unique_count'    => 0,
            'duplicate_count' => 0
        ]);

        foreach ($leadsData as $lead) {
            $isDup = $lead['is_duplicate'] ?? false;
            $rawPhone = $lead['Mob'] ?? ($lead['mob'] ?? '');
            $rawEmail = $lead['Email'] ?? ($lead['email'] ?? '');

            // Strict Validation Check
            $phoneRes = $this->sanitizer->cleanPhone($rawPhone);
            $emailRes = $this->sanitizer->cleanEmail($rawEmail);

            $phone = $phoneRes['value'];
            $email = $emailRes['value'];

            // Reject ONLY IF BOTH phone and email are invalid/blank!
            // If AT LEAST ONE contact method is valid (Phone OR Email), allow import!
            if (!$phoneRes['is_valid'] && !$emailRes['is_valid']) {
                $invalidCount++;
                continue;
            }

            // Set clean values (or empty string if invalid)
            $phone = $phoneRes['is_valid'] ? $phoneRes['value'] : '';
            $email = $emailRes['is_valid'] ? $emailRes['value'] : '';

            // Strict Database Deduplication: Reject any Phone OR Email already in DB
            $existsInDb = false;
            if (!empty($phone) && Lead::where('mob', $phone)->exists()) {
                $existsInDb = true;
            }
            if (!$existsInDb && !empty($email) && Lead::where('email', $email)->exists()) {
                $existsInDb = true;
            }

            if ($existsInDb) {
                $duplicateCount++;
                continue;
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
                'year'            => $lead['Year'] ?? ($lead['year'] ?? date('Y')),
                'quarter'         => $lead['Quarter'] ?? ($lead['quarter'] ?? 'Q1'),
                'name'            => $lead['Name'] ?? ($lead['name'] ?? ''),
                'mob'             => $phone,
                'email'           => $email,
                'raw_course'      => $lead['Raw_Course'] ?? ($lead['raw_course'] ?? ''),
                'major_category'  => $lead['Major_Category'] ?? ($lead['major_category'] ?? 'Other'),
                'source'          => $lead['Source'] ?? ($lead['source'] ?? 'Direct/Organic'),
                'status'          => $lead['Status'] ?? ($lead['status'] ?? ''),
                'notes'           => $lead['Notes'] ?? ($lead['notes'] ?? ''),
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
            'message'         => "Successfully saved {$insertedCount} clean authentic lead(s) to MySQL Vault! ({$duplicateCount} duplicates & {$invalidCount} invalid/blank skipped)",
            'batch_id'        => $batch->id,
            'saved_count'     => $insertedCount,
            'duplicate_count' => $duplicateCount,
            'invalid_count'   => $invalidCount
        ]);
    }

    /**
     * Fetch Stored Leads from Database with Advanced Filtering
     */
    public function getDatabaseLeads(Request $request)
    {
        $category = $request->input('category', 'ALL');
        $source   = $request->input('source', 'ALL');
        $year     = $request->input('year', 'ALL');
        $quarter  = $request->input('quarter', 'ALL');
        $status   = $request->input('status', 'ALL');
        $search   = $request->input('search', '');

        $query = Lead::query()->with('importBatch')->latest();

        if ($category !== 'ALL') {
            $query->where('major_category', $category);
        }

        if ($source !== 'ALL') {
            $query->where(function($q) use ($source) {
                $q->where('source', $source)->orWhere('source', 'like', "%{$source}%");
            });
        }

        if ($year !== 'ALL') {
            $query->where('year', $year);
        }

        if ($quarter !== 'ALL') {
            $query->where('quarter', $quarter);
        }

        if ($status !== 'ALL') {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mob', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('raw_course', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
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
     * Create Manual Lead (CRUD - Create)
     */
    public function storeLead(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'nullable|string|max:255',
            'mob'            => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'raw_course'     => 'nullable|string|max:255',
            'major_category' => 'nullable|string|max:255',
            'source'         => 'nullable|string|max:255',
            'status'         => 'nullable|string|max:255',
            'date'           => 'nullable|string|max:50',
            'notes'          => 'nullable|string',
        ]);

        $phone = $this->sanitizer->cleanPhone($validated['mob'] ?? '')['value'] ?? '';
        $email = $this->sanitizer->cleanEmail($validated['email'] ?? '')['value'] ?? '';

        if (!empty($phone) && Lead::where('mob', $phone)->exists()) {
            return response()->json([
                'success' => false,
                'error'   => "Lead with phone number {$phone} already exists in Database!"
            ], 422);
        }

        $dateInfo = $this->sanitizer->cleanDateAndMonth($validated['date'] ?? date('Y-m-d'));

        $lead = Lead::create([
            'sheet_name'     => 'Manual Entry',
            'date'           => $dateInfo['date'] ?: date('Y-m-d'),
            'month'          => $dateInfo['month'] ?: date('F'),
            'year'           => $dateInfo['year'] ?: date('Y'),
            'quarter'        => $dateInfo['quarter'] ?: 'Q1',
            'name'           => $this->sanitizer->cleanName($validated['name'] ?? ''),
            'mob'            => $phone,
            'email'          => $email,
            'raw_course'     => $validated['raw_course'] ?? '',
            'major_category' => $validated['major_category'] ?? $this->categorizer->categorize($validated['raw_course'] ?? ''),
            'source'         => $validated['source'] ?? 'Direct/Organic',
            'status'         => $validated['status'] ?? 'Fresh Lead',
            'notes'          => $validated['notes'] ?? '',
            'is_duplicate'   => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead created successfully!',
            'lead'    => $lead
        ]);
    }

    /**
     * Update Single Lead (CRUD - Update)
     */
    public function updateLead(Request $request, $id)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json(['success' => false, 'error' => 'Lead not found'], 404);
        }

        $validated = $request->validate([
            'name'           => 'nullable|string|max:255',
            'mob'            => 'nullable|string|max:50',
            'email'          => 'nullable|string|max:255',
            'raw_course'     => 'nullable|string|max:255',
            'major_category' => 'nullable|string|max:255',
            'source'         => 'nullable|string|max:255',
            'status'         => 'nullable|string|max:255',
            'date'           => 'nullable|string|max:50',
            'notes'          => 'nullable|string',
        ]);

        if (isset($validated['mob'])) {
            $phone = $this->sanitizer->cleanPhone($validated['mob'])['value'] ?? '';
            if (!empty($phone) && $phone !== $lead->mob && Lead::where('mob', $phone)->where('id', '!=', $id)->exists()) {
                return response()->json(['success' => false, 'error' => "Phone number {$phone} already assigned to another lead!"], 422);
            }
            $lead->mob = $phone;
        }

        if (isset($validated['name'])) $lead->name = $this->sanitizer->cleanName($validated['name']);
        if (isset($validated['email'])) $lead->email = $this->sanitizer->cleanEmail($validated['email'])['value'] ?? $validated['email'];
        if (isset($validated['raw_course'])) {
            $lead->raw_course = $validated['raw_course'];
            if (!isset($validated['major_category'])) {
                $lead->major_category = $this->categorizer->categorize($validated['raw_course']);
            }
        }
        if (isset($validated['major_category'])) $lead->major_category = $validated['major_category'];
        if (isset($validated['source'])) $lead->source = $validated['source'];
        if (isset($validated['status'])) $lead->status = $validated['status'];
        if (isset($validated['notes'])) $lead->notes = $validated['notes'];

        if (isset($validated['date'])) {
            $dateInfo = $this->sanitizer->cleanDateAndMonth($validated['date']);
            $lead->date    = $dateInfo['date'];
            $lead->month   = $dateInfo['month'];
            $lead->year    = $dateInfo['year'];
            $lead->quarter = $dateInfo['quarter'];
        }

        $lead->save();

        return response()->json([
            'success' => true,
            'message' => 'Lead updated successfully!',
            'lead'    => $lead
        ]);
    }

    /**
     * Delete Single Lead (CRUD - Delete)
     */
    public function deleteLead($id)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json(['success' => false, 'error' => 'Lead not found'], 404);
        }

        $lead->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lead record deleted successfully.'
        ]);
    }

    /**
     * Bulk Delete Selected Leads
     */
    public function bulkDeleteLeads(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return response()->json(['success' => false, 'error' => 'No lead IDs provided'], 400);
        }

        $count = Lead::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$count} selected leads from Database."
        ]);
    }

    /**
     * Bulk Update Status of Selected Leads
     */
    public function bulkUpdateStatus(Request $request)
    {
        $ids    = $request->input('ids', []);
        $status = $request->input('status', 'Enrolled');

        if (empty($ids) || !is_array($ids)) {
            return response()->json(['success' => false, 'error' => 'No lead IDs provided'], 400);
        }

        $count = Lead::whereIn('id', $ids)->update(['status' => $status]);

        return response()->json([
            'success' => true,
            'message' => "Successfully updated status of {$count} leads to '{$status}'."
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

    /**
     * Data Analyst BI Analytics Summary Endpoint
     */
    public function getAnalyticsSummary(Request $request)
    {
        $totalLeads     = Lead::count();
        $enrolledCount  = Lead::where('status', 'Enrolled')->count();
        $visitedCount   = Lead::where('status', 'Visited')->count();
        $freshCount     = Lead::where('status', 'Fresh Lead')->count();
        $noNeedCall     = Lead::where('status', 'No Need to Call')->count();

        // Breakdown by Year
        $yearBreakdown = Lead::select('year', DB::raw('count(*) as count'))
            ->whereNotNull('year')
            ->where('year', '!=', '')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();

        // Breakdown by Quarter
        $quarterBreakdown = Lead::select('quarter', DB::raw('count(*) as count'))
            ->whereNotNull('quarter')
            ->where('quarter', '!=', '')
            ->groupBy('quarter')
            ->orderBy('quarter', 'asc')
            ->get();

        // Breakdown by Category
        $categoryBreakdown = Lead::select('major_category', DB::raw('count(*) as count'))
            ->groupBy('major_category')
            ->get();

        // Breakdown by Source
        $sourceBreakdown = Lead::select('source', DB::raw('count(*) as count'))
            ->groupBy('source')
            ->orderBy('count', 'desc')
            ->get();

        // Breakdown by Status
        $statusBreakdown = Lead::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return response()->json([
            'success' => true,
            'metrics' => [
                'total_leads'     => $totalLeads,
                'enrolled'        => $enrolledCount,
                'visited'         => $visitedCount,
                'fresh'           => $freshCount,
                'no_need_to_call' => $noNeedCall,
                'conversion_rate' => $totalLeads > 0 ? round(($enrolledCount / $totalLeads) * 100, 1) : 0,
            ],
            'year_breakdown'     => $yearBreakdown,
            'quarter_breakdown'  => $quarterBreakdown,
            'category_breakdown' => $categoryBreakdown,
            'source_breakdown'   => $sourceBreakdown,
            'status_breakdown'   => $statusBreakdown,
        ]);
    }

    /**
     * Clear / Wipe All Database Leads & Import Batches (Requires Password Authentication)
     */
    public function wipeAllLeads(Request $request)
    {
        $password = $request->input('password', '');
        $user = auth()->user();

        if (empty($password)) {
            return response()->json([
                'success' => false,
                'error'   => 'Admin Security Password is required to perform database wipe!'
            ], 422);
        }

        // Verify password against logged-in user or master admin passwords
        $isValidPassword = false;
        if ($user && \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            $isValidPassword = true;
        } elseif ($password === '123456' || $password === 'admin123') {
            $isValidPassword = true;
        }

        if (!$isValidPassword) {
            return response()->json([
                'success' => false,
                'error'   => '🔒 Security Error: Invalid Admin Password! Access Denied.'
            ], 403);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Lead::truncate();
        ImportBatch::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return response()->json([
            'success' => true,
            'message' => '🧹 Security Verified! All database records and import history have been completely wiped.'
        ]);
    }

    /**
     * Search Single Student by Email Address
     */
    public function searchByEmail(Request $request)
    {
        $email = trim((string)$request->input('email', ''));

        if (empty($email)) {
            return response()->json([
                'success' => false,
                'error'   => 'Please provide an email address to search.'
            ], 400);
        }

        $leads = Lead::where('email', 'like', "%{$email}%")
            ->orWhere('email', $email)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'count'   => count($leads),
            'leads'   => $leads
        ]);
    }
}
