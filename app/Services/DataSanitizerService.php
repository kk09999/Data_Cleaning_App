<?php

namespace App\Services;

use DateTime;

class DataSanitizerService
{
    /**
     * Transliterate Hindi Devanagari characters to English Latin alphabet.
     */
    public function transliterateHindi(string $text): string
    {
        $map = [
            'क्ष' => 'ksh', 'त्र' => 'tr', 'ज्ञ' => 'gya', 'श्र' => 'shr',
            'क' => 'k', 'ख' => 'kh', 'ग' => 'g', 'घ' => 'gh', 'ङ' => 'n',
            'च' => 'ch', 'छ' => 'chh', 'ज' => 'j', 'झ' => 'jh', 'ञ' => 'n',
            'ट' => 't', 'ठ' => 'th', 'ड' => 'd', 'ढ' => 'dh', 'ण' => 'n',
            'त' => 't', 'थ' => 'th', 'द' => 'd', 'ध' => 'dh', 'न' => 'n',
            'प' => 'p', 'फ' => 'f', 'ब' => 'b', 'भ' => 'bh', 'म' => 'm',
            'य' => 'y', 'र' => 'r', 'ल' => 'l', 'व' => 'v', 'श' => 'sh', 'ष' => 'sh', 'स' => 's', 'ह' => 'h',
            'ड़' => 'd', 'ढ़' => 'dh',
            'अ' => 'a', 'आ' => 'aa', 'इ' => 'i', 'ई' => 'ee', 'उ' => 'u', 'ऊ' => 'oo', 'ऋ' => 'ri', 'ए' => 'e', 'ऐ' => 'ai', 'ओ' => 'o', 'औ' => 'au',
            'ा' => 'a', 'ि' => 'i', 'ी' => 'ee', 'ु' => 'u', 'ू' => 'oo', 'ृ' => 'ri', 'े' => 'e', 'ै' => 'ai', 'ो' => 'o', 'ौ' => 'au', 'ं' => 'n', 'ः' => 'h', '्' => ''
        ];

        return strtr($text, $map);
    }

    /**
     * Clean Name: Transliterate Hindi, strip special chars/numbers, format in Title Case.
     */
    public function cleanName(?string $rawName): string
    {
        if (empty($rawName) || !trim((string)$rawName)) {
            return '';
        }

        $str = trim((string)$rawName);

        // Transliterate Devanagari / Hindi Unicode characters
        if (preg_match('/[\x{0900}-\x{097F}]/u', $str)) {
            $str = $this->transliterateHindi($str);
        }

        // Remove numbers, punctuation, special symbols - keep A-Z, a-z and spaces only
        $cleaned = preg_replace('/[^a-zA-Z\s]/', ' ', $str);
        $cleaned = trim(preg_replace('/\s+/', ' ', $cleaned));

        if (empty($cleaned) || strlen($cleaned) < 2) {
            return '';
        }

        // Format as Title Case (Sentence/Capitalized Case)
        return ucwords(strtolower($cleaned));
    }

    /**
     * Clean & Validate Email: Convert to lowercase, fix domain typos, check syntax & dummy values.
     */
    public function cleanEmail(?string $rawEmail): array
    {
        if (empty($rawEmail) || !trim((string)$rawEmail)) {
            return ['value' => '', 'is_valid' => false, 'reason' => 'Blank Email'];
        }

        $email = strtolower(trim((string)$rawEmail));
        $email = str_replace(' ', '', $email);

        // Instant rejection of junk non-email strings: 0, -, --, cti, computer course, etc.
        $junk = ['0', '-', '--', 'n/a', 'na', 'null', 'undefined', 'none', 'blank', 'space', 'c', 'cti', 'computer course', 'bca'];
        if (in_array($email, $junk, true) || !str_contains($email, '@')) {
            return ['value' => '', 'is_valid' => false, 'reason' => 'Invalid Email Syntax'];
        }

        // Fix domain typos (e.g. wwanchinasangma@gamil.com -> wwanchinasangma@gmail.com)
        $email = preg_replace('/@gamill?\.com$/i', '@gmail.com', $email);
        $email = preg_replace('/@gamil\.com$/i', '@gmail.com', $email);
        $email = preg_replace('/@gmial\.com$/i', '@gmail.com', $email);
        $email = preg_replace('/@gmaill?\.com$/i', '@gmail.com', $email);
        $email = preg_replace('/@gmai\.com$/i', '@gmail.com', $email);
        $email = preg_replace('/@hotmaill?\.com$/i', '@hotmail.com', $email);
        $email = preg_replace('/@yahooo?\.com$/i', '@yahoo.com', $email);

        // Reject dummy or unauthorized email strings
        $dummyEmails = [
            'test@test.com', 'noemail@gmail.com', 'na@gmail.com', 'none@gmail.com',
            'null@gmail.com', 'abc@xyz.com', 'no@email.com', 'email@gmail.com',
            'xyz@gmail.com', 'dummy@gmail.com', 'sample@gmail.com', 'user@gmail.com'
        ];

        if (in_array($email, $dummyEmails, true)) {
            return ['value' => '', 'is_valid' => false, 'reason' => 'Dummy/Unauthorized Email'];
        }

        if (preg_match('/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i', $email)) {
            return ['value' => $email, 'is_valid' => true, 'reason' => ''];
        }

        return ['value' => '', 'is_valid' => false, 'reason' => 'Invalid Email Syntax'];
    }

    /**
     * Clean & Validate Phone/Mob: Extract last 10 digits from right side if >= 10 digits.
     * Supports Nepal and international numbers (7+ digits).
     */
    public function cleanPhone(?string $rawPhone): array
    {
        if (empty($rawPhone) || !trim((string)$rawPhone) || strtoupper(trim((string)$rawPhone)) === 'N/A') {
            return ['value' => '', 'is_valid' => false, 'reason' => 'Blank Phone'];
        }

        $str = trim((string)$rawPhone);
        $digits = preg_replace('/\D/', '', $str);

        if (empty($digits)) {
            return ['value' => '', 'is_valid' => false, 'reason' => 'Blank Phone'];
        }

        // Extract 10 digits from right side if >= 10 digits
        if (strlen($digits) >= 10) {
            $cleaned = substr($digits, -10);
            return ['value' => $cleaned, 'is_valid' => true, 'reason' => ''];
        }

        // Support Nepal & International numbers (7 to 9 digits)
        if (strlen($digits) >= 7) {
            return ['value' => $digits, 'is_valid' => true, 'reason' => ''];
        }

        return ['value' => $digits, 'is_valid' => false, 'reason' => 'Short Phone Number'];
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
        $monthShorts = ["jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec"];

        $year = '';
        $monthNum = 0;
        $day = 0;
        $formattedDate = '';

        $dateStr = trim((string)$rawDate);

        if (is_numeric($rawDate) && (float)$rawDate > 10000 && (float)$rawDate < 100000) {
            $unixTimestamp = ((float)$rawDate - 25569) * 86400;
            $dt = new DateTime("@$unixTimestamp");
            $formattedDate = $dt->format('d-M-Y');
            $monthNum = (int)$dt->format('n');
            $year = $dt->format('Y');
        } else {
            // Check textual month e.g. 31-Jan-2025 or 31-Jul-2025
            if (preg_match('/(\d{1,2})[\/\-\.\s]+([a-zA-Z]+)[\/\-\.\s]+(\d{2,4})/', $dateStr, $m)) {
                $day = (int)$m[1];
                $mStr = strtolower(substr($m[2], 0, 3));
                $mIdx = array_search($mStr, $monthShorts, true);
                if ($mIdx !== false) {
                    $monthNum = $mIdx + 1;
                }
                $y = (int)$m[3];
                if ($y < 100) $y += 2000;
                $year = (string)$y;
                if ($monthNum >= 1 && $monthNum <= 12) {
                    $formattedDate = sprintf('%02d-%s-%04d', $day, substr($monthNames[$monthNum - 1], 0, 3), $y);
                }
            } elseif (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})$/', $dateStr, $m)) {
                $day = (int)$m[1];
                $monthNum = (int)$m[2];
                $y = (int)$m[3];
                if ($y < 100) $y += 2000;
                $year = (string)$y;
                if ($monthNum >= 1 && $monthNum <= 12) {
                    $formattedDate = sprintf('%02d-%s-%04d', $day, substr($monthNames[$monthNum - 1], 0, 3), $y);
                }
            }

            if (empty($formattedDate)) {
                try {
                    $dt = new DateTime($dateStr);
                    if ((int)$dt->format('Y') > 1990 && (int)$dt->format('Y') < 2050) {
                        $formattedDate = $dt->format('d-M-Y');
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
     * Categorize raw course string based on exact attachment mapping rules.
     */
    public function categorizeCourse(?string $courseStr): string
    {
        if (empty($courseStr) || !trim((string)$courseStr)) {
            return 'Other';
        }

        $courseKey = strtolower(trim((string)$courseStr));

        // Exact Mappings from User Attachment
        $exactMap = [
            // Data Analyst and Scientist
            'c-da' => 'Data Analyst and Scientist',
            'm-da' => 'Data Analyst and Scientist',
            'ex-da' => 'Data Analyst and Scientist',
            'pyca' => 'Data Analyst and Scientist',
            'pbi' => 'Data Analyst and Scientist',
            'm-da+vba' => 'Data Analyst and Scientist',
            'data analytics' => 'Data Analyst and Scientist',
            'data science' => 'Data Analyst and Scientist',
            'pbi+asql' => 'Data Analyst and Scientist',
            'ad excel' => 'Data Analyst and Scientist',
            'ad ex+tally' => 'Data Analyst and Scientist',
            'ad ex+pbi' => 'Data Analyst and Scientist',
            'python' => 'Data Analyst and Scientist',
            'asql' => 'Data Analyst and Scientist',
            'mern+ad ex' => 'Data Analyst and Scientist',
            'mern+ad excel' => 'Data Analyst and Scientist',
            'mso+ad excel' => 'Data Analyst and Scientist',
            'mis' => 'Data Analyst and Scientist',
            'ad excel+mso' => 'Data Analyst and Scientist',
            'mso+m-da' => 'Data Analyst and Scientist',
            'cds' => 'Data Analyst and Scientist',
            'cgai' => 'Data Analyst and Scientist',
            'pcds' => 'Data Analyst and Scientist',
            'ai' => 'Data Analyst and Scientist',
            'da+cgai' => 'Data Analyst and Scientist',
            'mda' => 'Data Analyst and Scientist',
            'da+data science' => 'Data Analyst and Scientist',
            'ex' => 'Data Analyst and Scientist',
            'vda' => 'Data Analyst and Scientist',
            'sql & python' => 'Data Analyst and Scientist',
            'python+ai' => 'Data Analyst and Scientist',
            'python advance' => 'Data Analyst and Scientist',
            'ex+sql' => 'Data Analyst and Scientist',
            'ex+python' => 'Data Analyst and Scientist',
            'computer training for accounting' => 'Data Analyst and Scientist',
            'mis+pbi' => 'Data Analyst and Scientist',
            'python+ai+ml' => 'Data Analyst and Scientist',
            'ex+da+r' => 'Data Analyst and Scientist',
            'mso+mis' => 'Data Analyst and Scientist',
            'python core' => 'Data Analyst and Scientist',
            'sql' => 'Data Analyst and Scientist',
            'ai+ml' => 'Data Analyst and Scientist',
            'sql+pbi+tableau' => 'Data Analyst and Scientist',
            'sql+pbi+tereau' => 'Data Analyst and Scientist',
            'ad excel+typing' => 'Data Analyst and Scientist',
            'chat gpt' => 'Data Analyst and Scientist',
            'mso+cda' => 'Data Analyst and Scientist',
            'ad excel+pay roll' => 'Data Analyst and Scientist',
            'ot' => 'Data Analyst and Scientist',
            'corporate training' => 'Data Analyst and Scientist',
            'da+pcds' => 'Data Analyst and Scientist',
            'mda+cgai' => 'Data Analyst and Scientist',

            // Accounting and Taxation
            'tally gst' => 'Accounting and Taxation',
            'gst+itr' => 'Accounting and Taxation',
            'cea' => 'Accounting and Taxation',
            'cfa-pro' => 'Accounting and Taxation',
            'tally' => 'Accounting and Taxation',
            'cea-a' => 'Accounting and Taxation',
            'tds+gst' => 'Accounting and Taxation',
            'itr e-filing' => 'Accounting and Taxation',
            'cea-p' => 'Accounting and Taxation',
            'gst+tds' => 'Accounting and Taxation',
            'umna' => 'Accounting and Taxation',
            'gst' => 'Accounting and Taxation',
            'accounting' => 'Accounting and Taxation',
            'itr' => 'Accounting and Taxation',
            'caf' => 'Accounting and Taxation',
            'gst e-filing' => 'Accounting and Taxation',
            'acaf' => 'Accounting and Taxation',
            'mso+tally' => 'Accounting and Taxation',
            'mcaf' => 'Accounting and Taxation',
            'acct for taxation' => 'Accounting and Taxation',
            'sap' => 'Accounting and Taxation',
            'busy' => 'Accounting and Taxation',
            'tally+busy+gst' => 'Accounting and Taxation',
            'acaa' => 'Accounting and Taxation',
            'tally gst+e-filing+itr' => 'Accounting and Taxation',
            'cfa-p' => 'Accounting and Taxation',
            'cea pro' => 'Accounting and Taxation',

            // Full Stack Developer
            'wd+java' => 'Full Stack Developer',
            'wd+pyca' => 'Full Stack Developer',
            'digital mkt' => 'Full Stack Developer',
            'wd' => 'Full Stack Developer',
            'full stack+mern' => 'Full Stack Developer',
            'wd+php' => 'Full Stack Developer',
            'digital marketing' => 'Full Stack Developer',
            'c & c++' => 'Full Stack Developer',
            'c' => 'Full Stack Developer',
            'programming' => 'Full Stack Developer',
            'bca' => 'Full Stack Developer',
            '.net' => 'Full Stack Developer',
            'mern' => 'Full Stack Developer',
            'java core+ad' => 'Full Stack Developer',
            'c++' => 'Full Stack Developer',
            'c++ & java' => 'Full Stack Developer',
            'java' => 'Full Stack Developer',
            'ncad' => 'Full Stack Developer',
            'mcrn' => 'Full Stack Developer',
            'react js' => 'Full Stack Developer',
            '.net+sql' => 'Full Stack Developer',
            'dsa' => 'Full Stack Developer',
            'c++ & pyca' => 'Full Stack Developer',
            'wd+mern+dsa' => 'Full Stack Developer',
            'seo' => 'Full Stack Developer',
            'wd+tally' => 'Full Stack Developer',
            'wd+node' => 'Full Stack Developer',
            'c & java' => 'Full Stack Developer',
            'wd+mern' => 'Full Stack Developer',
            'wd+python' => 'Full Stack Developer',
            'c-da+mso' => 'Full Stack Developer',
            'c & c++-python' => 'Full Stack Developer',

            // Other
            'unspecified / direct course' => 'Other',
            'computer course' => 'Other',
            'ms office' => 'Other',
            'mso' => 'Other',
            'cd' => 'Other',
            'other course' => 'Other',
            'ms ofc' => 'Other',
            'ccc' => 'Other',
            'cti' => 'Other',
            'other' => 'Other',
            'o level' => 'Other',
            'computer typing' => 'Other',
            'pgdca' => 'Other',
            'coreldraw' => 'Other',
            'ppc' => 'Other',
            'english' => 'Other',
            'mso+gd' => 'Other',
            'photoshop' => 'Other',
            'ccc+' => 'Other',
            'bcc' => 'Other',
            'placement' => 'Other',
            'mso+wd' => 'Other',
            'web' => 'Other',
            'computer' => 'Other',
            'ecc' => 'Other'
        ];

        if (isset($exactMap[$courseKey])) {
            return $exactMap[$courseKey];
        }

        // Smart Pattern Fallback
        if (preg_match('/tally|gst|itr|tds|accounting|tax|cea|cfa|caf|busy|sap/i', $courseKey)) {
            return 'Accounting and Taxation';
        }
        if (preg_match('/da|data|ai|python|pbi|power bi|sql|excel|mis|analytics|science|cgai|vda|cds|mda|chat gpt/i', $courseKey)) {
            return 'Data Analyst and Scientist';
        }
        if (preg_match('/wd|web|mern|react|node|java|c\+\+|dsa|developer|programming|bca|full stack|digital marketing|seo|\.net/i', $courseKey)) {
            return 'Full Stack Developer';
        }

        return 'Other';
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
