<?php

namespace App\Services;

class CourseCategorizerService
{
    const DATA_ANALYST = 'Data Analyst and Scientist';
    const ACCOUNTING   = 'Accounting and Taxation';
    const DEVELOPER    = 'Full Stack Developer';
    const OTHER        = 'Other';

    /**
     * Exact user course mappings dictionary
     */
    protected array $courseMap = [
        // --- 1. Data Analyst and Scientist ---
        'MDA'               => self::DATA_ANALYST,
        'MIS+ASQ'           => self::DATA_ANALYST,
        'MIS+ASQL'          => self::DATA_ANALYST,
        'C-DA'              => self::DATA_ANALYST,
        'M-DA'              => self::DATA_ANALYST,
        'EX-DA'             => self::DATA_ANALYST,
        'EX'                => self::DATA_ANALYST,
        'ORACLE'            => self::DATA_ANALYST,
        'DA+DATA SCIENCE'   => self::DATA_ANALYST,
        'AD EX+PBI'         => self::DATA_ANALYST,
        'CBDA'              => self::DATA_ANALYST,
        'MIS+PBI'           => self::DATA_ANALYST,
        'PBI'               => self::DATA_ANALYST,
        'MIS'               => self::DATA_ANALYST,
        'ASQL'              => self::DATA_ANALYST,
        'BSQL'              => self::DATA_ANALYST,
        'M-DA+VBA'          => self::DATA_ANALYST,
        'AD EX+PBI+ASQL'    => self::DATA_ANALYST,
        'MSO+MIS'           => self::DATA_ANALYST,
        'ADVANCE EXCEL+VBA' => self::DATA_ANALYST,
        'MSO+AD EXCEL'      => self::DATA_ANALYST,
        'ACBDA'             => self::DATA_ANALYST,
        'DATA ANALYTICS'    => self::DATA_ANALYST,
        'EX+SQL'            => self::DATA_ANALYST,
        'MCDA'              => self::DATA_ANALYST,
        'AD EX+PBI+BSQL'    => self::DATA_ANALYST,
        'EX+PBI+ASQL'       => self::DATA_ANALYST,
        'MCBDA'             => self::DATA_ANALYST,
        'MDA+CDS'           => self::DATA_ANALYST,
        'MDA+PCDS'          => self::DATA_ANALYST,
        'MDA+CGAI'          => self::DATA_ANALYST,
        'EX+VBA'            => self::DATA_ANALYST,
        'CDS'               => self::DATA_ANALYST,
        'PCDS'              => self::DATA_ANALYST,
        'CGA'               => self::DATA_ANALYST,
        'CGAI'              => self::DATA_ANALYST,
        'AI'                => self::DATA_ANALYST,
        'DA+CGAI'           => self::DATA_ANALYST,
        'MSO+M-DA'          => self::DATA_ANALYST,
        'MIS+TALLY'         => self::DATA_ANALYST,
        'PYCA'              => self::DATA_ANALYST,
        'DATA SCIENCE'      => self::DATA_ANALYST,
        'PBI+MI'            => self::DATA_ANALYST,
        'PBI+ASQL'          => self::DATA_ANALYST,
        'ADVANCE EXCEL'     => self::DATA_ANALYST,
        'ADVANCE PPT'       => self::DATA_ANALYST,
        'MASTER-DA'         => self::DATA_ANALYST,

        // --- 2. Accounting and Taxation ---
        'TALLY GST'         => self::ACCOUNTING,
        'CEA-P'             => self::ACCOUNTING,
        'CEA-A'             => self::ACCOUNTING,
        'CEA-PRO'           => self::ACCOUNTING,
        'CEA'               => self::ACCOUNTING,
        'GST E-FILING'      => self::ACCOUNTING,
        'ITR'               => self::ACCOUNTING,
        'CMR'               => self::ACCOUNTING,
        'ACATP'             => self::ACCOUNTING,
        'ACAF'              => self::ACCOUNTING,
        'MCAF'              => self::ACCOUNTING,
        'PCATA'             => self::ACCOUNTING,
        'TALLY GST+GST E-FILING' => self::ACCOUNTING,
        'TALLY GST+GST E-FIL+ITR'=> self::ACCOUNTING,
        'TALLY+BUSY'        => self::ACCOUNTING,
        'CMFA'              => self::ACCOUNTING,
        'MSO+TALLY'         => self::ACCOUNTING,
        'AD EX+TALLY'       => self::ACCOUNTING,
        'SAP'               => self::ACCOUNTING,
        'SAS'               => self::ACCOUNTING,
        'MCATP'             => self::ACCOUNTING,
        'ACFAR'             => self::ACCOUNTING,
        'CCAA-E-TAX'        => self::ACCOUNTING,
        'BUSY'              => self::ACCOUNTING,
        'CAF'               => self::ACCOUNTING,
        'COMPUTER TRAINING FOR ACCOUNTING' => self::ACCOUNTING,
        'GST TRAINING INS'  => self::ACCOUNTING,
        'INST FOR TAXATION' => self::ACCOUNTING,
        'CTI'               => self::ACCOUNTING,
        'TALLY GST TRAINING INSTITUTE' => self::ACCOUNTING,
        'TALLY GST+E-FILING+ITR' => self::ACCOUNTING,
        'CAA'               => self::ACCOUNTING,
        'ACAA'              => self::ACCOUNTING,
        'MCAA'              => self::ACCOUNTING,
        'GD+TALLY'          => self::ACCOUNTING,
        'MCCA'              => self::ACCOUNTING,
        'GD+TALLY+GST EFLING'=> self::ACCOUNTING,
        'GST+ITR'           => self::ACCOUNTING,
        'VBA'               => self::ACCOUNTING,

        // --- 3. Full Stack Developer ---
        '.NET'              => self::DEVELOPER,
        'PHP'               => self::DEVELOPER,
        'WD'                => self::DEVELOPER,
        'WD+PHP'            => self::DEVELOPER,
        'PYTHON CORE'       => self::DEVELOPER,
        'PYTHON ADVANCE'    => self::DEVELOPER,
        'PYC'               => self::DEVELOPER,
        'JAVA CORE +AD'     => self::DEVELOPER,
        'JAVA CORE'         => self::DEVELOPER,
        'C'                 => self::DEVELOPER,
        'C++'               => self::DEVELOPER,
        'MERN'              => self::DEVELOPER,
        'MEAN'              => self::DEVELOPER,
        'MCAD'              => self::DEVELOPER,
        'HTML+CSS+JAVA'     => self::DEVELOPER,
        'WD+PYCA'           => self::DEVELOPER,
        'WD+JAVA'           => self::DEVELOPER,
        'WD+.NET'           => self::DEVELOPER,
        'C++ & JAVA'        => self::DEVELOPER,
        'C C++ & PYCA'      => self::DEVELOPER,
        'PBI+SQL+PYCA'      => self::DEVELOPER,
        'DSA'               => self::DEVELOPER,
        'C C++'             => self::DEVELOPER,
        'C & C++'           => self::DEVELOPER,
        'WD+.NET+DSA'       => self::DEVELOPER,
        'WD+JAVA+PHP'       => self::DEVELOPER,
        'WD+PHP+DSA'        => self::DEVELOPER,
        'JAVA'              => self::DEVELOPER,

        // --- 4. Other ---
        'ACC'               => self::OTHER,
        'CCC'               => self::OTHER,
        'HARDWARE'          => self::OTHER,
        'ECC'               => self::OTHER,
        'IP XII'            => self::OTHER,
        'IP XII SUBJECTS'   => self::OTHER,
        'ENGLISH'           => self::OTHER,
        'CCC+'              => self::OTHER,
        'BCC'               => self::OTHER,
        'DIGITAL MARKETING' => self::OTHER,
        'MSO+GD'            => self::OTHER,
        'PHOTOSHOP'         => self::OTHER,
        'CORELDRAW'         => self::OTHER,
        'ILLUSTRATOR'       => self::OTHER,
        'CPGWD'             => self::OTHER,
        'CORPORATE TRAINING'=> self::OTHER,
        'COMPUTER TYPING'   => self::OTHER,
        'MSO'               => self::OTHER,
        'PPT'               => self::OTHER,
        'GD'                => self::OTHER,
        'JOB'               => self::OTHER
    ];

    public function categorize(?string $courseInput, array $customMap = []): string
    {
        if (empty($courseInput)) {
            return self::OTHER;
        }

        $rawUpper = strtoupper(trim($courseInput));

        if (isset($customMap[$rawUpper])) {
            return $customMap[$rawUpper];
        }

        if (isset($this->courseMap[$rawUpper])) {
            return $this->courseMap[$rawUpper];
        }

        $normalized = trim(preg_replace('/\s+/', ' ', str_replace(['"', "'", '&'], ' ', $rawUpper)));
        if (isset($this->courseMap[$normalized])) {
            return $this->courseMap[$normalized];
        }

        // Keyword rules
        if (
            str_contains($normalized, 'MDA') ||
            str_contains($normalized, 'M-DA') ||
            str_contains($normalized, 'DATA ANALYTIC') ||
            str_contains($normalized, 'DATA SCIENCE') ||
            str_contains($normalized, 'POWER BI') ||
            str_contains($normalized, 'POWERBI') ||
            str_contains($normalized, 'EXCEL') ||
            str_contains($normalized, 'SQL') ||
            str_contains($normalized, 'CGAI') ||
            $normalized === 'AI' ||
            str_contains($normalized, 'AI ') ||
            str_contains($normalized, ' AI')
        ) {
            return self::DATA_ANALYST;
        }

        if (
            str_contains($normalized, 'TALLY') ||
            str_contains($normalized, 'GST') ||
            str_contains($normalized, 'TAX') ||
            str_contains($normalized, 'ACCOUNT') ||
            str_contains($normalized, 'ITR') ||
            str_contains($normalized, 'BUSY') ||
            str_contains($normalized, 'SAP')
        ) {
            return self::ACCOUNTING;
        }

        if (
            str_contains($normalized, 'DEVELOP') ||
            str_contains($normalized, 'FULL STACK') ||
            str_contains($normalized, 'MERN') ||
            str_contains($normalized, 'MEAN') ||
            str_contains($normalized, 'JAVA') ||
            str_contains($normalized, 'PHP') ||
            str_contains($normalized, '.NET') ||
            str_contains($normalized, 'DSA') ||
            str_contains($normalized, 'C++')
        ) {
            return self::DEVELOPER;
        }

        return self::OTHER;
    }
}
