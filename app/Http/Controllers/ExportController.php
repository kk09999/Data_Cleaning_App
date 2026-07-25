<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;

class ExportController extends Controller
{
    /**
     * Export leads to CSV, omitting rows where phone or email is blank.
     */
    public function exportCsv(Request $request)
    {
        $filename = 'leads_export_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($request) {
            $handle = fopen('php://output', 'w');
            // Header row
            fputcsv($handle, ['ID', 'Date', 'Month', 'Name', 'Phone', 'Email', 'Course', 'Category', 'Source']);

            // Query leads, filter out blank phone or email
            Lead::whereNotNull('mob')
                ->where('mob', '!=', '')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->orderBy('id')
                ->chunk(200, function ($leads) use ($handle) {
                    foreach ($leads as $lead) {
                        fputcsv($handle, [
                            $lead->id,
                            $lead->date,
                            $lead->month,
                            $lead->name,
                            $lead->mob,
                            $lead->email,
                            $lead->raw_course,
                            $lead->major_category,
                            $lead->source,
                        ]);
                    }
                });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
