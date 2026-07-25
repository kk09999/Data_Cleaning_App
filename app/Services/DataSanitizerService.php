<?php

namespace App\Services;

use DateTime;

class DataSanitizerService
{
    /**
     * Clean Name: English words only (a-z, A-Z and spaces).
     */
    public function cleanName(?string $rawName): string
    {
        if (empty($rawName)) {
            return '';
        }

        $cleaned = preg_replace('/[^a-zA-Z\s]/', '', (string)$rawName);
        $cleaned = trim(preg_replace('/\s+/', ' ', $cleaned));

        if (empty($cleaned)) {
            return '';
        }

        return ucwords(strtolower($cleaned));
    }

    /**
     * Clean Email: Convert to lowercase, strip invalid spacing, fix common domain typos.
     * Returns empty string '' if missing/invalid.
     */
    public function cleanEmail(?string $rawEmail): array
    {
        if (empty($rawEmail) || !trim((string)$rawEmail)) {
            return ['value' => '', 'is_valid' => false];
        }

        $email = strtolower(trim((string)$rawEmail));
        $email = str_replace(' ', '', $email);

        $email = preg_replace('/@gmaill?\.com$/i', '@gmail.com', $email);
        $email = preg_replace('/@gmai\.com$/i', '@gmail.com', $email);

        if (preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $email, $matches)) {
            return ['value' => $matches[0], 'is_valid' => true];
        }

        return ['value' => '', 'is_valid' => false];
    }

    /**
     * Clean Phone/Mob: Extract digits WITHOUT + or +91 prefix.
     * Returns empty string '' if missing/invalid.
     */
    public function cleanPhone(?string $rawPhone): array
    {
        if (empty($rawPhone) || !trim((string)$rawPhone)) {
            return ['value' => '', 'is_valid' => false];
        }

        $digits = preg_replace('/\D/', '', (string)$rawPhone);

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) >= 7) {
            return ['value' => $digits, 'is_valid' => true];
        }

        return ['value' => '', 'is_valid' => false];
    }

    /**
     * Clean Date and Extract Month Name, Year, and Quarter cleanly.
     */
    public function cleanDateAndMonth($rawDate): array
    {
        if (empty($rawDate)) {
            return ['date' => '', 'month' => '', 'year' => '', 'quarter' => ''];
        }

        $monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

        $year = '';
        $monthNum = 0;
        $formattedDate = '';

        if (is_numeric($rawDate) && (float)$rawDate > 10000 && (float)$rawDate < 100000) {
            $unixTimestamp = ((float)$rawDate - 25569) * 86400;
            $dt = new DateTime("@$unixTimestamp");
            $formattedDate = $dt->format('Y-m-d');
            $monthNum = (int)$dt->format('n');
            $year = $dt->format('Y');
        } else {
            $dateStr = trim((string)$rawDate);

            if (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})$/', $dateStr, $m)) {
                $day = (int)$m[1];
                $monthNum = (int)$m[2];
                $y = (int)$m[3];
                if ($y < 100) $y += 2000;

                if ($monthNum >= 1 && $monthNum <= 12 && $day >= 1 && $day <= 31) {
                    $formattedDate = sprintf('%04d-%02d-%02d', $y, $monthNum, $day);
                    $year = (string)$y;
                }
            }

            if (empty($formattedDate)) {
                try {
                    $dt = new DateTime($dateStr);
                    if ((int)$dt->format('Y') > 1990 && (int)$dt->format('Y') < 2050) {
                        $formattedDate = $dt->format('Y-m-d');
                        $monthNum = (int)$dt->format('n');
                        $year = $dt->format('Y');
                    }
                } catch (\Exception $e) {
                    $formattedDate = $dateStr;
                }
            }
        }

        $monthName = ($monthNum >= 1 && $monthNum <= 12) ? $monthNames[$monthNum - 1] : '';
        $quarter = '';
        if ($monthNum >= 1 && $monthNum <= 3) $quarter = 'Q1';
        elseif ($monthNum >= 4 && $monthNum <= 6) $quarter = 'Q2';
        elseif ($monthNum >= 7 && $monthNum <= 9) $quarter = 'Q3';
        elseif ($monthNum >= 10 && $monthNum <= 12) $quarter = 'Q4';

        return [
            'date'    => $formattedDate,
            'month'   => $monthName,
            'year'    => $year,
            'quarter' => $quarter
        ];
    }

    /**
     * Detect Lead Status dynamically from row content.
     */
    public function detectStatus(array $row): string
    {
        $allText = strtolower(implode(' ', array_values($row)));

        if (str_contains($allText, 'enrolled')) {
            return 'Enrolled';
        }
        if (str_contains($allText, 'visited')) {
            return 'Visited';
        }
        if (str_contains($allText, 'no need to call') || str_contains($allText, 'not interested')) {
            return 'No Need to Call';
        }
        if (str_contains($allText, 'interested') || str_contains($allText, 'demo')) {
            return 'Interested';
        }
        if (str_contains($allText, 'followup') || str_contains($allText, 'follow up')) {
            return 'Follow-up';
        }

        return 'Fresh Lead';
    }

    /**
     * Do NOT exclude rows during upload (Upload 100% of data for analytics).
     */
    public function shouldExcludeRow(array $row): bool
    {
        return false;
    }

    /**
     * Check if entire row is empty
     */
    public function isRowEmpty(array $row): bool
    {
        foreach ($row as $k => $val) {
            if ($k === '_sheet_name') continue;
            if (!empty($val) && trim((string)$val) !== '') {
                return false;
            }
        }
        return true;
    }
}
