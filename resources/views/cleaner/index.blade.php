@extends('layouts.app')

@section('content')
<div x-data="cleanerApp" x-init="initData()" style="display: flex; gap: 25px; align-items: flex-start;">

    <!-- LEFT SIDEBAR NAVIGATION MENU -->
    <div style="width: 260px; flex-shrink: 0; background: white; border-radius: 16px; padding: 20px 14px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(5,25,77,0.05); position: sticky; top: 90px;">
        <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; padding: 6px 12px 10px 12px;">
            NAVIGATION MENU
        </div>

        <div style="display: flex; flex-direction: column; gap: 6px;">
            <button 
                class="btn" 
                :class="activeTab === 'analytics' ? 'btn-primary' : 'btn-outline'"
                style="justify-content: flex-start; width: 100%; border-radius: 10px; padding: 11px 16px; font-size: 13.5px;"
                @click="activeTab = 'analytics'; $nextTick(() => renderCharts())"
            >
                <i data-lucide="bar-chart-3" style="width:18px"></i> BI Analytics
            </button>

            <button 
                class="btn" 
                :class="activeTab === 'cleaner' ? 'btn-primary' : 'btn-outline'"
                style="justify-content: flex-start; width: 100%; border-radius: 10px; padding: 11px 16px; font-size: 13.5px;"
                @click="activeTab = 'cleaner'"
            >
                <i data-lucide="wand-2" style="width:18px"></i> Lead Importer
            </button>

            <button 
                class="btn" 
                :class="activeTab === 'vault' ? 'btn-primary' : 'btn-outline'"
                style="justify-content: flex-start; width: 100%; border-radius: 10px; padding: 11px 16px; font-size: 13.5px;"
                @click="activeTab = 'vault'; loadDatabaseVault()"
            >
                <i data-lucide="database" style="width:18px"></i> MySQL Vault (<span x-text="dbLeads.length"></span>)
            </button>

            <button 
                class="btn" 
                :class="activeTab === 'student_search' ? 'btn-primary' : 'btn-outline'"
                style="justify-content: flex-start; width: 100%; border-radius: 10px; padding: 11px 16px; font-size: 13.5px;"
                @click="activeTab = 'student_search'"
            >
                <i data-lucide="user-search" style="width:18px"></i> Student Lookup
            </button>

            <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; padding: 16px 12px 6px 12px;">
                TOOLS & ACTIONS
            </div>

            <button 
                class="btn btn-gold" 
                style="justify-content: flex-start; width: 100%; border-radius: 10px; padding: 11px 16px; font-size: 13.5px;"
                @click="openExportModal(activeTab === 'vault' ? 'vault' : 'current')"
            >
                <i data-lucide="sliders" style="width:18px"></i> Dynamic Exporter
            </button>

            <button 
                class="btn" 
                style="justify-content: flex-start; width: 100%; border-radius: 10px; padding: 11px 16px; font-size: 13.5px; background: #7c3aed; color: white;"
                @click="exportUniqueCoursesReport()"
            >
                <i data-lucide="graduation-cap" style="width:18px"></i> Export Unique Courses
            </button>

            <button 
                class="btn" 
                style="justify-content: flex-start; width: 100%; border-radius: 10px; padding: 11px 16px; font-size: 13px; background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5;"
                @click="wipeAllDatabaseLeads()"
            >
                <i data-lucide="trash-2" style="width:18px"></i> Wipe Database
            </button>
        </div>
    </div>

    <!-- MAIN RIGHT VIEW CONTAINER -->
    <div style="flex: 1; min-width: 0;">

        <!-- TAB 1: Cleaner & Multi-Sheet Aggregator -->
        <div x-show="activeTab === 'cleaner'">
            <div class="card-panel">
                <div class="card-title">
                    <i data-lucide="upload" style="width:22px; color:var(--primary-navy)"></i> Multi-Sheet Lead Importer
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-bottom: 20px;">
                    <div 
                        :style="importType === 'file' ? 'border-color: var(--accent-blue); background:#f0f7ff;' : ''"
                        style="border: 2px dashed #cbd5e1; border-radius: 12px; padding: 20px; text-align: center; background: #f8fafc; cursor: pointer;"
                        @click="importType = 'file'"
                    >
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: #e0edff; color: var(--primary-navy); display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto;">
                            <i data-lucide="file-spreadsheet" style="width:20px"></i>
                        </div>
                        <h3 style="font-size: 14px; font-weight: 600; margin-bottom: 4px;">Excel / CSV File</h3>
                        <p style="font-size: 11.5px; color: #64748b;">Compiles all sheets inside workbook</p>
                    </div>

                    <div 
                        :style="importType === 'google' ? 'border-color: var(--accent-blue); background:#f0f7ff;' : ''"
                        style="border: 2px dashed #cbd5e1; border-radius: 12px; padding: 20px; text-align: center; background: #f8fafc; cursor: pointer;"
                        @click="importType = 'google'"
                    >
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: #e0edff; color: var(--primary-navy); display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto;">
                            <i data-lucide="link" style="width:20px"></i>
                        </div>
                        <h3 style="font-size: 14px; font-weight: 600; margin-bottom: 4px;">Google Sheet URL</h3>
                        <p style="font-size: 11.5px; color: #64748b;">Paste public Google Sheet link</p>
                    </div>

                    <div 
                        style="border: 2px dashed #fef08a; border-radius: 12px; padding: 20px; text-align: center; background: #fefce8; cursor: pointer;"
                        @click="loadSampleDataset()"
                    >
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: #fef08a; color: #854d0e; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto;">
                            <i data-lucide="sparkles" style="width:20px"></i>
                        </div>
                        <h3 style="font-size: 14px; font-weight: 600; margin-bottom: 4px; color: #854d0e;">Try Demo Dataset</h3>
                        <p style="font-size: 11.5px; color: #a16207;">Test Lead Source & Deduplication</p>
                    </div>
                </div>

                <template x-if="importType === 'file'">
                    <div style="padding: 20px; background: #f8fafc; border-radius: 10px; border: 1px solid var(--border-color); text-align: center;">
                        <label style="display: inline-block; cursor: pointer;">
                            <span class="btn btn-primary" style="padding: 12px 28px; font-size: 15px;">
                                <i data-lucide="upload" style="width:18px"></i> Choose Excel File (.xlsx, .xls, .csv)
                            </span>
                            <input type="file" accept=".xlsx, .xls, .csv" @change="handleFileUpload($event)" style="display: none;">
                        </label>
                    </div>
                </template>

                <template x-if="successMsg">
                    <div style="margin-top: 15px; padding: 12px 16px; background: #f0fdf4; border-left: 4px solid #22c55e; color: #166534; border-radius: 6px; font-size: 13px;">
                        <span x-text="successMsg"></span>
                    </div>
                </template>
            </div>

            <!-- STEP 2: COLUMN BINDING PANEL -->
            <template x-if="headers.length > 0">
                <div class="card-panel" style="background: #f8fafc; border: 1px solid #bfdbfe;">
                    <div class="card-title" style="font-size: 16px;">
                        <i data-lucide="sliders" style="width:20px; color:var(--accent-blue)"></i> Column Header Binding & Verification
                    </div>
                    <p style="font-size: 12.5px; color: #64748b; margin-bottom: 15px;">
                        Verify auto-detected header columns. Adjust mapping below if needed:
                    </p>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 4px;">👤 Student Name Column:</label>
                            <select x-model="mappings.nameCol" @change="runCleaningEngine()" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 13px; font-weight: 600;">
                                <option value="">-- Ignore / None --</option>
                                <template x-for="h in headers" :key="h">
                                    <option :value="h" x-text="h"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 4px;">📞 Phone / Mobile Column:</label>
                            <select x-model="mappings.phoneCol" @change="runCleaningEngine()" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 13px; font-weight: 600;">
                                <option value="">-- Ignore / None --</option>
                                <template x-for="h in headers" :key="h">
                                    <option :value="h" x-text="h"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 4px;">✉️ Email Address Column:</label>
                            <select x-model="mappings.emailCol" @change="runCleaningEngine()" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 13px; font-weight: 600;">
                                <option value="">-- Ignore / None --</option>
                                <template x-for="h in headers" :key="h">
                                    <option :value="h" x-text="h"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 4px;">🎓 Course / Stream Column:</label>
                            <select x-model="mappings.courseCol" @change="runCleaningEngine()" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 13px; font-weight: 600;">
                                <option value="">-- Ignore / None --</option>
                                <template x-for="h in headers" :key="h">
                                    <option :value="h" x-text="h"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 4px;">🏷️ Final Status Column:</label>
                            <select x-model="mappings.statusCol" @change="runCleaningEngine()" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 13px; font-weight: 600;">
                                <option value="">-- Ignore / Use Sheet Raw Text --</option>
                                <template x-for="h in headers" :key="h">
                                    <option :value="h" x-text="h"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 4px;">🌐 Lead Source Column:</label>
                            <select x-model="mappings.sourceCol" @change="runCleaningEngine()" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 13px; font-weight: 600;">
                                <option value="">-- Ignore / None --</option>
                                <template x-for="h in headers" :key="h">
                                    <option :value="h" x-text="h"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 4px;">📅 Date Column:</label>
                            <select x-model="mappings.dateCol" @change="runCleaningEngine()" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 13px; font-weight: 600;">
                                <option value="">-- Ignore / None --</option>
                                <template x-for="h in headers" :key="h">
                                    <option :value="h" x-text="h"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Importer Leads Table & Pagination -->
            <template x-if="processedData.length > 0">
                <div class="card-panel">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 700; color: var(--primary-navy);">
                                Cleaned Lead Dataset (<span x-text="processedData.length"></span> Total Records)
                            </h3>
                        </div>

                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <label style="font-size: 12px; font-weight: 600;">Rows per page:</label>
                            <select x-model.number="pageSize" @change="page = 1" style="padding: 6px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12px; font-weight: 600;">
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="500">500</option>
                            </select>

                            <button class="btn btn-primary" @click="saveToDatabaseVault()">
                                Save Unique Leads to MySQL Vault
                            </button>
                        </div>
                    </div>

                    <div class="table-wrapper">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Month</th>
                                    <th>Year</th>
                                    <th>Quarter</th>
                                    <th>Name</th>
                                    <th>Mob</th>
                                    <th>Email</th>
                                    <th>Course</th>
                                    <th>Major Category</th>
                                    <th>Source</th>
                                    <th>Final Status</th>
                                    <th>Validation Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, idx) in paginatedRows" :key="idx">
                                    <tr :class="{'duplicate-row': row.is_duplicate || row.is_invalid_contact}">
                                        <td style="color:#64748b" x-text="((page - 1) * pageSize) + idx + 1"></td>
                                        <td style="font-size: 12px;" x-text="row.Date"></td>
                                        <td><strong style="color:var(--primary-navy)" x-text="row.Month"></strong></td>
                                        <td style="font-size: 12px;" x-text="row.Year"></td>
                                        <td><span style="background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:700" x-text="row.Quarter"></span></td>
                                        <td style="font-weight: 600;" x-text="row.Name || 'N/A'"></td>
                                        <td><span style="font-family:monospace; font-weight:600" x-text="row.Mob || 'N/A'"></span></td>
                                        <td><span style="font-weight:600" x-text="row.Email || 'N/A'"></span></td>
                                        <td><code x-text="row.Raw_Course || 'N/A'"></code></td>
                                        <td><span class="badge-cat badge-da" x-text="row.Major_Category"></span></td>
                                        <td><span style="background:#e0edff; color:var(--primary-navy); padding:2px 10px; border-radius:12px; font-size:11px; font-weight:700" x-text="row.Source"></span></td>
                                        <td>
                                            <span 
                                                :style="row.Status === 'Will Visit' || row.Status === 'Visited' ? 'background:#fef3c7; color:#d97706' : (row.Status === 'Will Confirm' || row.Status === 'Enrolled' ? 'background:#dcfce7; color:#16a34a' : (row.Status === 'NP' || row.Status === 'Ph Dis' || row.Status === 'Out of Service' || row.Status === 'Switch Off' ? 'background:#fee2e2; color:#dc2626' : 'background:#e0edff; color:#1e40af'))"
                                                style="padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700;" 
                                                x-text="row.Status">
                                            </span>
                                        </td>
                                        <td>
                                            <template x-if="row.is_invalid_contact">
                                                <span style="background: #fee2e2; color: #dc2626; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;" x-text="row.invalid_reason"></span>
                                            </template>
                                            <template x-if="!row.is_invalid_contact && row.is_duplicate">
                                                <span style="background: #fef3c7; color: #d97706; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">Dup (Phone/Email)</span>
                                            </template>
                                            <template x-if="!row.is_invalid_contact && !row.is_duplicate">
                                                <span style="background: #dcfce7; color: #16a34a; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">Valid Authentic Lead</span>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; flex-wrap: wrap; gap: 10px;">
                        <div style="font-size: 13px; color: #64748b;">
                            Showing page <strong x-text="page"></strong> of <strong x-text="totalPages"></strong> (<span x-text="processedData.length"></span> total leads)
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn btn-outline" style="padding: 6px 14px; font-size: 12px;" @click="page = Math.max(1, page - 1)" :disabled="page === 1">
                                &larr; Previous
                            </button>
                            <button class="btn btn-outline" style="padding: 6px 14px; font-size: 12px;" @click="page = Math.min(totalPages, page + 1)" :disabled="page === totalPages">
                                Next &rarr;
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- TAB 2: Saved Database Vault & CRUD Hub -->
        <div x-show="activeTab === 'vault'">
            <div class="card-panel">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <h2 style="font-size: 18px; font-weight: 700; color: var(--primary-navy); margin: 0; display: flex; align-items: center; gap: 8px;">
                            <i data-lucide="database" style="width:22px"></i> Saved MySQL Database Vault (<span x-text="filteredDbLeads.length"></span> Records)
                        </h2>
                    </div>

                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <button class="btn btn-primary" @click="openAddLeadModal()">
                            <i data-lucide="plus-circle" style="width:16px"></i> ➕ Add Lead
                        </button>
                        <button class="btn" style="background:#7c3aed; color:white;" @click="exportUniqueCoursesReport()">
                            <i data-lucide="graduation-cap" style="width:16px"></i> 🎓 Unique Courses
                        </button>
                        <button class="btn btn-gold" @click="openExportModal('vault')">
                            <i data-lucide="sliders" style="width:16px"></i> Custom Export
                        </button>
                    </div>
                </div>

                <!-- Multi-Filter Bar inside Vault -->
                <div style="background: #f8fafc; border-radius: 10px; padding: 16px 20px; border: 1px solid var(--border-color); margin-bottom: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 4px;">🎓 Category:</label>
                        <select x-model="vaultCategoryFilter" style="padding: 7px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px; font-weight: 600;">
                            <option value="ALL">All Categories</option>
                            <option value="Data Analyst and Scientist">Data Analyst & Scientist</option>
                            <option value="Accounting and Taxation">Accounting & Taxation</option>
                            <option value="Full Stack Developer">Full Stack Developer</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 4px;">🏷️ Final Status:</label>
                        <select x-model="vaultStatusFilter" style="padding: 7px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px; font-weight: 600;">
                            <option value="ALL">All Statuses</option>
                            <option value="Will Visit">Will Visit</option>
                            <option value="Will Confirm">Will Confirm</option>
                            <option value="NP">NP (No Pick)</option>
                            <option value="Call Later">Call Later</option>
                            <option value="Enrolled">Enrolled</option>
                            <option value="Visited">Visited</option>
                        </select>
                    </div>

                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 4px;">🌐 Source:</label>
                        <select x-model="vaultSourceFilter" style="padding: 7px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px; font-weight: 600;">
                            <option value="ALL">All Sources</option>
                            <option value="Google">Google</option>
                            <option value="Facebook">Facebook</option>
                            <option value="Instagram">Instagram</option>
                            <option value="Website">Website</option>
                            <option value="WhatsApp">WhatsApp</option>
                        </select>
                    </div>

                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 4px;">📅 Year:</label>
                        <select x-model="vaultYearFilter" style="padding: 7px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px; font-weight: 600;">
                            <option value="ALL">All Years</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                        </select>
                    </div>

                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 4px;">🗓️ Quarter:</label>
                        <select x-model="vaultQuarterFilter" style="padding: 7px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px; font-weight: 600;">
                            <option value="ALL">All Quarters</option>
                            <option value="Q1">Q1 (Jan - Mar)</option>
                            <option value="Q2">Q2 (Apr - Jun)</option>
                            <option value="Q3">Q3 (Jul - Sep)</option>
                            <option value="Q4">Q4 (Oct - Dec)</option>
                        </select>
                    </div>
                </div>

                <!-- Vault Table -->
                <div class="table-wrapper">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Month</th>
                                <th>Year</th>
                                <th>Quarter</th>
                                <th>Name</th>
                                <th>Mob</th>
                                <th>Email</th>
                                <th>Course</th>
                                <th>Major Category</th>
                                <th>Source</th>
                                <th>Final Status</th>
                                <th style="text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="filteredDbLeads.length === 0">
                                <tr>
                                    <td colSpan="13" style="text-align:center; padding:30px; color:#64748b">
                                        No database records match selected filters.
                                    </td>
                                </tr>
                            </template>
                            <template x-for="(lead, idx) in filteredDbLeads" :key="lead.id || idx">
                                <tr>
                                    <td style="color:#64748b" x-text="idx + 1"></td>
                                    <td style="font-size: 12px;" x-text="lead.Date || lead.date"></td>
                                    <td><strong style="color:var(--primary-navy)" x-text="lead.Month || lead.month"></strong></td>
                                    <td style="font-size: 12px;" x-text="lead.Year || lead.year"></td>
                                    <td><span style="background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:700" x-text="lead.Quarter || lead.quarter || 'Q1'"></span></td>
                                    <td style="font-weight: 600;" x-text="lead.Name || lead.name"></td>
                                    <td style="font-family:monospace; font-weight:600" x-text="lead.Mob || lead.mob"></td>
                                    <td style="font-weight:600" x-text="lead.Email || lead.email"></td>
                                    <td><code x-text="lead.Raw_Course || lead.raw_course"></code></td>
                                    <td><span class="badge-cat badge-da" x-text="lead.Major_Category || lead.major_category || 'Other'"></span></td>
                                    <td><span style="background:#e0edff; color:var(--primary-navy); padding:2px 10px; border-radius:12px; font-size:11px; font-weight:700" x-text="lead.Source || lead.source || 'Direct/Organic'"></span></td>
                                    <td>
                                        <span 
                                            :style="(lead.Status || lead.status) === 'Enrolled' || (lead.Status || lead.status) === 'Will Confirm' ? 'background:#dcfce7; color:#16a34a' : (((lead.Status || lead.status) === 'Visited' || (lead.Status || lead.status) === 'Will Visit' ? 'background:#fef3c7; color:#d97706' : 'background:#f1f5f9; color:#475569'))"
                                            style="padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700;" 
                                            x-text="lead.Status || lead.status">
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 6px; justify-content: center;">
                                            <button class="btn btn-outline" style="padding: 4px 8px; font-size: 11px;" @click="openEditLeadModal(lead)">
                                                ✏️ Edit
                                            </button>
                                            <button class="btn" style="background: #fee2e2; color: #dc2626; border: none; padding: 4px 8px; font-size: 11px; border-radius: 6px;" @click="deleteSingleLead(lead.id)">
                                                🗑️
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- NEW TAB: SINGLE STUDENT SEARCH & DELETE BY EMAIL -->
        <div x-show="activeTab === 'student_search'">
            <div class="card-panel">
                <div style="margin-bottom: 20px;">
                    <h2 style="font-size: 20px; font-weight: 800; color: var(--primary-navy); display: flex; align-items: center; gap: 10px;">
                        <i data-lucide="user-search" style="width:24px; color:var(--accent-blue)"></i> Single Student Search & Quick Delete
                    </h2>
                    <p style="color: #64748b; font-size: 13px; margin-top: 4px;">
                        Enter a student's email address below to inspect full record details and delete or update that single student.
                    </p>
                </div>

                <!-- Search Input Box -->
                <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid var(--border-color); margin-bottom: 25px;">
                    <label style="display: block; font-size: 13px; font-weight: 700; color: var(--primary-navy); margin-bottom: 8px;">
                        Search Student by Email Address:
                    </label>
                    <div style="display: flex; gap: 12px;">
                        <input 
                            type="text" 
                            placeholder="Enter email e.g. rahul.sharma@gmail.com or priya..." 
                            x-model="studentSearchEmail" 
                            @keyup.enter="searchStudentByEmail()"
                            style="flex: 1; padding: 12px 18px; border-radius: 50px; border: 1px solid var(--border-color); font-size: 14px; outline: none; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);"
                        >
                        <button class="btn btn-primary" @click="searchStudentByEmail()" style="padding: 12px 28px;">
                            <i data-lucide="search" style="width:18px"></i> Search Student
                        </button>
                    </div>
                </div>

                <!-- Search Results Area -->
                <template x-if="searchedStudentResults.length > 0">
                    <div>
                        <div style="margin-bottom: 15px; font-size: 14px; font-weight: 700; color: var(--primary-navy);">
                            Found <span x-text="searchedStudentResults.length"></span> Matching Student Record(s):
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
                            <template x-for="st in searchedStudentResults" :key="st.id">
                                <div style="background: white; border-radius: 14px; padding: 22px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                        <div>
                                            <h3 style="font-size: 17px; font-weight: 700; color: var(--primary-navy);" x-text="st.name || 'Unnamed Student'"></h3>
                                            <div style="font-size: 13px; font-weight: 600; color: var(--accent-blue);" x-text="st.email || 'No Email Provided'"></div>
                                        </div>
                                        <span 
                                            :style="st.status === 'Enrolled' ? 'background:#dcfce7; color:#16a34a' : (st.status === 'Visited' ? 'background:#fef3c7; color:#d97706' : 'background:#f1f5f9; color:#475569')"
                                            style="padding: 3px 12px; border-radius: 12px; font-size: 12px; font-weight: 700;" 
                                            x-text="st.status">
                                        </span>
                                    </div>

                                    <div style="font-size: 13px; color: #475569; display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 20px; background: #f8fafc; padding: 14px; border-radius: 10px;">
                                        <div>📞 Phone: <strong style="font-family:monospace;" x-text="st.mob"></strong></div>
                                        <div>🎓 Course: <strong x-text="st.raw_course"></strong></div>
                                        <div>🌐 Source: <strong x-text="st.source"></strong></div>
                                        <div>📅 Date: <strong x-text="st.date"></strong></div>
                                        <div>🗓️ Year/Qtr: <strong x-text="(st.year || '') + ' ' + (st.quarter || '')"></strong></div>
                                        <div>🏷️ Category: <strong x-text="st.major_category"></strong></div>
                                    </div>

                                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                        <button class="btn btn-outline" style="padding: 7px 16px; font-size: 12px;" @click="openEditLeadModal(st)">
                                            ✏️ Edit Student
                                        </button>
                                        <button class="btn" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 7px 16px; font-size: 12px; border-radius: 50px;" @click="deleteSingleStudentSearched(st.id, st.email)">
                                            <i data-lucide="trash-2" style="width:14px"></i> Delete Student
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- TAB 3: EXECUTIVE POWER-BI STYLE ANALYTICS DASHBOARD -->
        <div x-show="activeTab === 'analytics'">
            
            <!-- Dashboard Header Title & Quick Actions Bar -->
            <div style="background: linear-gradient(135deg, #05194d 0%, #082a7d 100%); border-radius: 16px; padding: 24px 28px; margin-bottom: 25px; color: white; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; box-shadow: 0 10px 30px rgba(5,25,77,0.2);">
                <div>
                    <h2 style="font-size: 22px; font-weight: 800; color: white; display: flex; align-items: center; gap: 10px; margin: 0;">
                        <i data-lucide="layout-dashboard" style="width:26px; color:#38bdf8"></i> CONTOSO - Lead Volume & Conversion Dashboard
                    </h2>
                    <p style="color: #93c5fd; font-size: 13px; margin-top: 4px;">
                        Real-time Data Intelligence Engine & Performance Visualizations
                    </p>
                </div>

                <div style="display: flex; gap: 12px;">
                    <button class="btn" style="background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.3); font-size: 13px;" @click="renderCharts()">
                        <i data-lucide="refresh-cw" style="width:16px"></i> Refresh Charts
                    </button>
                    <button class="btn btn-gold" @click="openExportModal('vault')">
                        <i data-lucide="download" style="width:16px"></i> Export BI Report
                    </button>
                </div>
            </div>

            <!-- Top Row: 5 KPI Summary Executive Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 25px;">
                <!-- Total Leads Card -->
                <div style="background: white; border-radius: 14px; padding: 20px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Leads</span>
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="users" style="width:18px"></i>
                        </div>
                    </div>
                    <div style="font-size: 30px; font-weight: 800; color: var(--primary-navy);" x-text="analyticsMetrics.total_leads || (dbLeads.length || processedData.length || 0)"></div>
                    <div style="font-size: 11.5px; color: #16a34a; font-weight: 700; margin-top: 4px;">📈 100% Compiled Sheet Data</div>
                </div>

                <!-- Confirmed / Enrolled Card -->
                <div style="background: white; border-radius: 14px; padding: 20px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: #15803d; text-transform: uppercase;">Enrolled & Confirmed</span>
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="check-circle" style="width:18px"></i>
                        </div>
                    </div>
                    <div style="font-size: 30px; font-weight: 800; color: #15803d;" x-text="analyticsMetrics.enrolled || 0"></div>
                    <div style="font-size: 11.5px; color: #15803d; font-weight: 700; margin-top: 4px;">🎯 High Intent Conversion</div>
                </div>

                <!-- Visited & Demos Card -->
                <div style="background: white; border-radius: 14px; padding: 20px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: #b45309; text-transform: uppercase;">Visited & Scheduled</span>
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="map-pin" style="width:18px"></i>
                        </div>
                    </div>
                    <div style="font-size: 30px; font-weight: 800; color: #b45309;" x-text="analyticsMetrics.visited || 0"></div>
                    <div style="font-size: 11.5px; color: #d97706; font-weight: 700; margin-top: 4px;">🚗 Campus Visits Completed</div>
                </div>

                <!-- Actionable Follow-ups -->
                <div style="background: white; border-radius: 14px; padding: 20px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: #1d4ed8; text-transform: uppercase;">Active Follow-ups</span>
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="phone-call" style="width:18px"></i>
                        </div>
                    </div>
                    <div style="font-size: 30px; font-weight: 800; color: #1d4ed8;" x-text="analyticsMetrics.active_followups || 0"></div>
                    <div style="font-size: 11.5px; color: #2563eb; font-weight: 700; margin-top: 4px;">📞 Will Visit / Call Later</div>
                </div>

                <!-- Cold / No Need to Call Card -->
                <div style="background: white; border-radius: 14px; padding: 20px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: #b91c1c; text-transform: uppercase;">No Need to Call</span>
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="x-circle" style="width:18px"></i>
                        </div>
                    </div>
                    <div style="font-size: 30px; font-weight: 800; color: #b91c1c;" x-text="analyticsMetrics.no_need_to_call || 0"></div>
                    <div style="font-size: 11.5px; color: #dc2626; font-weight: 700; margin-top: 4px;">🚫 Cold & Excluded Data</div>
                </div>
            </div>

            <!-- DASHBOARD FILTER BAR & CHARTS GRID -->
            <div style="display: flex; gap: 20px; align-items: flex-start;">
                
                <!-- Left PowerBI-Style Control Panel Filters -->
                <div style="width: 240px; flex-shrink: 0; background: white; border-radius: 14px; padding: 20px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                    <div style="font-size: 13px; font-weight: 800; color: var(--primary-navy); margin-bottom: 15px; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="filter" style="width:16px"></i> Dashboard Filters
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <div>
                            <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">📅 Select Year:</label>
                            <select x-model="dashYearFilter" @change="renderCharts()" style="width: 100%; padding: 7px 10px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 12.5px; font-weight: 600;">
                                <option value="ALL">All Years</option>
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                                <option value="2026">2026</option>
                            </select>
                        </div>

                        <div>
                            <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">🗓️ Select Quarter:</label>
                            <select x-model="dashQuarterFilter" @change="renderCharts()" style="width: 100%; padding: 7px 10px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 12.5px; font-weight: 600;">
                                <option value="ALL">All Quarters</option>
                                <option value="Q1">Q1 (Jan - Mar)</option>
                                <option value="Q2">Q2 (Apr - Jun)</option>
                                <option value="Q3">Q3 (Jul - Sep)</option>
                                <option value="Q4">Q4 (Oct - Dec)</option>
                            </select>
                        </div>

                        <div>
                            <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">🎓 Major Category:</label>
                            <select x-model="dashCategoryFilter" @change="renderCharts()" style="width: 100%; padding: 7px 10px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 12.5px; font-weight: 600;">
                                <option value="ALL">All Categories</option>
                                <option value="Data Analyst and Scientist">Data Analyst & Scientist</option>
                                <option value="Accounting and Taxation">Accounting & Taxation</option>
                                <option value="Full Stack Developer">Full Stack Developer</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">🌐 Lead Source:</label>
                            <select x-model="dashSourceFilter" @change="renderCharts()" style="width: 100%; padding: 7px 10px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 12.5px; font-weight: 600;">
                                <option value="ALL">All Sources</option>
                                <option value="Google">Google</option>
                                <option value="Facebook">Facebook</option>
                                <option value="Instagram">Instagram</option>
                                <option value="Website">Website</option>
                                <option value="WhatsApp">WhatsApp</option>
                            </select>
                        </div>

                        <div>
                            <label style="font-size: 11.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">🏷️ Final Status:</label>
                            <select x-model="dashStatusFilter" @change="renderCharts()" style="width: 100%; padding: 7px 10px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 12.5px; font-weight: 600;">
                                <option value="ALL">All Statuses</option>
                                <option value="Will Visit">Will Visit</option>
                                <option value="Will Confirm">Will Confirm</option>
                                <option value="NP">NP (No Pick)</option>
                                <option value="Call Later">Call Later</option>
                                <option value="Enrolled">Enrolled</option>
                                <option value="Visited">Visited</option>
                            </select>
                        </div>

                        <button class="btn btn-outline" style="width: 100%; margin-top: 5px; font-size: 12px; padding: 8px;" @click="resetDashFilters()">
                            🔄 Reset Filters
                        </button>
                    </div>
                </div>

                <!-- Right 2x3 Grid of Interactive Charts -->
                <div style="flex: 1; display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">

                    <!-- Chart 1: Donut Chart - Leads by Major Category -->
                    <div style="background: white; border-radius: 14px; padding: 20px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                        <div style="font-size: 14px; font-weight: 700; color: var(--primary-navy); margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between;">
                            <span>🍩 Leads by Major Category</span>
                            <span style="font-size: 11px; background: #e0f2fe; color: #0284c7; padding: 2px 8px; border-radius: 10px; font-weight: 700;">Category Share</span>
                        </div>
                        <div style="height: 240px; position: relative;">
                            <canvas id="chartCategory"></canvas>
                        </div>
                    </div>

                    <!-- Chart 2: Donut Chart - Final Status Breakdown -->
                    <div style="background: white; border-radius: 14px; padding: 20px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                        <div style="font-size: 14px; font-weight: 700; color: var(--primary-navy); margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between;">
                            <span>🍩 Final Status Distribution</span>
                            <span style="font-size: 11px; background: #dcfce7; color: #16a34a; padding: 2px 8px; border-radius: 10px; font-weight: 700;">Status Breakdown</span>
                        </div>
                        <div style="height: 240px; position: relative;">
                            <canvas id="chartStatus"></canvas>
                        </div>
                    </div>

                    <!-- Chart 3: Horizontal Bar Chart - Top Lead Sources -->
                    <div style="background: white; border-radius: 14px; padding: 20px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                        <div style="font-size: 14px; font-weight: 700; color: var(--primary-navy); margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between;">
                            <span>📊 Top Lead Sources Performance</span>
                            <span style="font-size: 11px; background: #fef3c7; color: #d97706; padding: 2px 8px; border-radius: 10px; font-weight: 700;">Lead Inflow</span>
                        </div>
                        <div style="height: 240px; position: relative;">
                            <canvas id="chartSource"></canvas>
                        </div>
                    </div>

                    <!-- Chart 4: Monthly Trend Line Chart -->
                    <div style="background: white; border-radius: 14px; padding: 20px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                        <div style="font-size: 14px; font-weight: 700; color: var(--primary-navy); margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between;">
                            <span>📈 Monthly Lead Volume Trend</span>
                            <span style="font-size: 11px; background: #f3e8ff; color: #7c3aed; padding: 2px 8px; border-radius: 10px; font-weight: 700;">Timeline</span>
                        </div>
                        <div style="height: 240px; position: relative;">
                            <canvas id="chartMonthlyTrend"></canvas>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>

    <!-- DYNAMIC CUSTOM EXPORT MODAL -->
    <div x-show="exportModalOpen" style="position: fixed; inset: 0; background: rgba(5,25,77,0.7); backdrop-filter: blur(6px); z-index: 999; display: flex; align-items: center; justify-content: center; padding: 20px;" x-transition>
        <div style="background: white; border-radius: 18px; width: 100%; max-width: 650px; padding: 28px; box-shadow: 0 25px 50px rgba(0,0,0,0.3);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                <h3 style="font-size: 18px; font-weight: 800; color: var(--primary-navy); display: flex; align-items: center; gap: 8px;">
                    🎯 Custom Dynamic Lead Exporter
                </h3>
                <button @click="exportModalOpen = false" style="background: none; border: none; font-size: 22px; cursor: pointer; color: #64748b;">&times;</button>
            </div>

            <!-- Export Filters Bar (Category, Status, Source) -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 18px; background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid var(--border-color);">
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 4px;">🎓 Category Filter:</label>
                    <select x-model="exportCategoryFilter" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px;">
                        <option value="ALL">All Categories</option>
                        <option value="Data Analyst and Scientist">Data Analyst & Scientist</option>
                        <option value="Accounting and Taxation">Accounting & Taxation</option>
                        <option value="Full Stack Developer">Full Stack Developer</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div>
                    <label style="font-size: 12px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 4px;">🏷️ Final Status Filter:</label>
                    <select x-model="exportStatusFilter" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px;">
                        <option value="ALL">All Statuses</option>
                        <option value="Will Visit">Will Visit</option>
                        <option value="Will Confirm">Will Confirm</option>
                        <option value="NP">NP (No Pick)</option>
                        <option value="Call Later">Call Later</option>
                        <option value="Enrolled">Enrolled</option>
                        <option value="Visited">Visited</option>
                    </select>
                </div>

                <div>
                    <label style="font-size: 12px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 4px;">🌐 Source Filter:</label>
                    <select x-model="exportSourceFilter" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px;">
                        <option value="ALL">All Sources</option>
                        <option value="Google">Google</option>
                        <option value="Facebook">Facebook</option>
                        <option value="Instagram">Instagram</option>
                        <option value="Website">Website</option>
                        <option value="WhatsApp">WhatsApp</option>
                    </select>
                </div>
            </div>

            <!-- Ignore / Exclusion Rules Panel -->
            <div style="margin-bottom: 20px; background: #eff6ff; border: 1px solid #bfdbfe; padding: 16px; border-radius: 12px;">
                <div style="font-size: 13px; font-weight: 800; color: #1e40af; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                    🛡️ Automatic Lead Exclusion Rules during Export:
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; font-size: 12.5px; color: #1e3a8a;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                        <input type="checkbox" x-model="exportIgnoreEnrolled" style="width: 16px; height: 16px;">
                        🚫 Ignore / Exclude "Enrolled"
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                        <input type="checkbox" x-model="exportIgnoreNoNeedToCall" style="width: 16px; height: 16px;">
                        🚫 Ignore / Exclude "No Need to Call"
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                        <input type="checkbox" x-model="exportIgnoreStudentData" style="width: 16px; height: 16px;">
                        🚫 Ignore / Exclude "Student Data"
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                        <input type="checkbox" x-model="exportSkipBlankContact" style="width: 16px; height: 16px;">
                        🚫 Exclude Rows Missing Phone OR Email
                    </label>
                </div>
            </div>

            <!-- Column Checkboxes -->
            <div style="margin-bottom: 20px;">
                <label style="font-size: 13px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 8px;">
                    Select Columns to Include in Export File:
                </label>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; font-size: 12.5px;">
                    <label><input type="checkbox" x-model="exportCols.name"> Name</label>
                    <label><input type="checkbox" x-model="exportCols.mob"> Phone / Mob</label>
                    <label><input type="checkbox" x-model="exportCols.email"> Email Address</label>
                    <label><input type="checkbox" x-model="exportCols.course"> Course</label>
                    <label><input type="checkbox" x-model="exportCols.category"> Major Category</label>
                    <label><input type="checkbox" x-model="exportCols.source"> Lead Source</label>
                    <label><input type="checkbox" x-model="exportCols.status"> Final Status</label>
                    <label><input type="checkbox" x-model="exportCols.date"> Date</label>
                    <label><input type="checkbox" x-model="exportCols.year"> Year & Quarter</label>
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; border-top: 1px solid var(--border-color); padding-top: 15px; flex-wrap: wrap;">
                <button class="btn btn-outline" @click="exportModalOpen = false">Cancel</button>
                <button class="btn" style="background:#7c3aed; color:white;" @click="exportUniqueCoursesReport()">🎓 Export Unique Courses (.xlsx)</button>
                <button class="btn btn-primary" @click="triggerStreamCsvExport()">⚡ Stream CSV Download</button>
                <button class="btn btn-gold" @click="triggerDynamicExport()">📊 Download Excel (.xlsx)</button>
            </div>
        </div>
    </div>

</div>

<script>
const cleanerDataDefinition = {
    activeTab: 'analytics',
    importType: 'file',
    fileName: '',
    headers: [],
    rawDataset: [],
    processedData: [],
    dbLeads: [],

    page: 1,
    pageSize: 50,

    mappings: {
        nameCol: '',
        phoneCol: '',
        emailCol: '',
        dateCol: '',
        courseCol: '',
        sourceCol: '',
        statusCol: ''
    },

    // DASHBOARD FILTERS STATE
    dashYearFilter: 'ALL',
    dashQuarterFilter: 'ALL',
    dashCategoryFilter: 'ALL',
    dashSourceFilter: 'ALL',
    dashStatusFilter: 'ALL',

    vaultCategoryFilter: 'ALL',
    vaultSourceFilter: 'ALL',
    vaultYearFilter: 'ALL',
    vaultQuarterFilter: 'ALL',
    vaultStatusFilter: 'ALL',

    studentSearchEmail: '',
    searchedStudentResults: [],
    searchedStudentTriggered: false,

    editLeadModalOpen: false,
    editLeadForm: { id: null, name: '', mob: '', email: '', raw_course: '', source: '', status: '', date: '' },

    analyticsMetrics: {},
    chartInstances: {},

    // EXPORT MODAL STATE WITH EXCLUSION RULES
    exportModalOpen: false,
    exportTarget: 'vault',
    exportCategoryFilter: 'ALL',
    exportStatusFilter: 'ALL',
    exportSourceFilter: 'ALL',
    exportIgnoreEnrolled: true,
    exportIgnoreNoNeedToCall: true,
    exportIgnoreStudentData: true,
    exportSkipBlankContact: true,
    exportCols: {
        name: true,
        mob: true,
        email: true,
        course: true,
        category: true,
        source: true,
        status: true,
        date: true,
        year: true
    },

    initData() {
        this.loadDatabaseVault();
        this.loadAnalyticsSummary();
        setTimeout(() => this.renderCharts(), 500);
    },

    resetDashFilters() {
        this.dashYearFilter = 'ALL';
        this.dashQuarterFilter = 'ALL';
        this.dashCategoryFilter = 'ALL';
        this.dashSourceFilter = 'ALL';
        this.dashStatusFilter = 'ALL';
        this.renderCharts();
    },

    renderCharts() {
        if (typeof Chart === 'undefined') return;

        let dataset = this.dbLeads.length > 0 ? this.dbLeads : this.processedData;
        if (!dataset || dataset.length === 0) return;

        // Filter dataset by dashboard filters
        let filtered = dataset.filter(r => {
            const cat = r.Major_Category || r.major_category;
            const src = String(r.Source || r.source || '').trim();
            const yr  = String(r.Year || r.year || '');
            const qtr = String(r.Quarter || r.quarter || '');
            const st  = String(r.Status || r.status || '').trim();

            if (this.dashCategoryFilter !== 'ALL' && cat !== this.dashCategoryFilter) return false;
            if (this.dashSourceFilter !== 'ALL' && !src.toLowerCase().includes(this.dashSourceFilter.toLowerCase())) return false;
            if (this.dashStatusFilter !== 'ALL' && !st.toLowerCase().includes(this.dashStatusFilter.toLowerCase())) return false;
            if (this.dashYearFilter !== 'ALL' && yr !== this.dashYearFilter) return false;
            if (this.dashQuarterFilter !== 'ALL' && qtr !== this.dashQuarterFilter) return false;

            return true;
        });

        // Update Dynamic Header Metrics
        let enrolledCount = filtered.filter(r => String(r.Status || r.status || '').toLowerCase().includes('enrol') || String(r.Status || r.status || '').toLowerCase().includes('confirm')).length;
        let visitedCount = filtered.filter(r => String(r.Status || r.status || '').toLowerCase().includes('visit')).length;
        let activeFollowups = filtered.filter(r => {
            let s = String(r.Status || r.status || '').toLowerCase();
            return s.includes('will') || s.includes('call') || s.includes('expected') || s.includes('next');
        }).length;
        let noNeedCount = filtered.filter(r => {
            let s = String(r.Status || r.status || '').toLowerCase();
            return s.includes('no need') || s.includes('np') || s.includes('dis') || s.includes('off') || s.includes('service');
        }).length;

        this.analyticsMetrics = {
            total_leads: filtered.length,
            enrolled: enrolledCount,
            visited: visitedCount,
            active_followups: activeFollowups,
            no_need_to_call: noNeedCount
        };

        // 1. Major Category Donut Chart
        const catCounts = {};
        filtered.forEach(r => {
            const c = r.Major_Category || r.major_category || 'Other';
            catCounts[c] = (catCounts[c] || 0) + 1;
        });

        if (this.chartInstances.category) this.chartInstances.category.destroy();
        const ctxCat = document.getElementById('chartCategory');
        if (ctxCat) {
            this.chartInstances.category = new Chart(ctxCat, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(catCounts),
                    datasets: [{
                        data: Object.values(catCounts),
                        backgroundColor: ['#0284c7', '#059669', '#7c3aed', '#d97706', '#64748b'],
                        borderWidth: 2
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });
        }

        // 2. Final Status Distribution Donut Chart
        const stCounts = {};
        filtered.forEach(r => {
            const s = r.Status || r.status || 'Blank / Direct';
            if (s) stCounts[s] = (stCounts[s] || 0) + 1;
        });
        const topStKeys = Object.keys(stCounts).sort((a,b) => stCounts[b] - stCounts[a]).slice(0, 7);

        if (this.chartInstances.status) this.chartInstances.status.destroy();
        const ctxSt = document.getElementById('chartStatus');
        if (ctxSt) {
            this.chartInstances.status = new Chart(ctxSt, {
                type: 'doughnut',
                data: {
                    labels: topStKeys,
                    datasets: [{
                        data: topStKeys.map(k => stCounts[k]),
                        backgroundColor: ['#2467ec', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899'],
                        borderWidth: 2
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });
        }

        // 3. Top Lead Sources Horizontal Bar Chart
        const srcCounts = {};
        filtered.forEach(r => {
            const s = r.Source || r.source || 'Direct/Organic';
            srcCounts[s] = (srcCounts[s] || 0) + 1;
        });
        const srcKeys = Object.keys(srcCounts).sort((a,b) => srcCounts[b] - srcCounts[a]).slice(0, 6);

        if (this.chartInstances.source) this.chartInstances.source.destroy();
        const ctxSrc = document.getElementById('chartSource');
        if (ctxSrc) {
            this.chartInstances.source = new Chart(ctxSrc, {
                type: 'bar',
                data: {
                    labels: srcKeys,
                    datasets: [{
                        label: 'Total Inflow Leads',
                        data: srcKeys.map(k => srcCounts[k]),
                        backgroundColor: '#0284c7',
                        borderRadius: 6
                    }]
                },
                options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        }

        // 4. Monthly Lead Volume Trend Line Chart (Smooth Spline Area)
        const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        const monthlyData = months.map(m => {
            return filtered.filter(r => (r.Month || r.month) === m).length;
        });

        if (this.chartInstances.trend) this.chartInstances.trend.destroy();
        const ctxTrend = document.getElementById('chartMonthlyTrend');
        if (ctxTrend) {
            this.chartInstances.trend = new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    datasets: [{
                        label: 'Monthly Lead Inflow',
                        data: monthlyData,
                        tension: 0.4,
                        fill: true,
                        backgroundColor: 'rgba(36, 103, 236, 0.12)',
                        borderColor: '#2467ec',
                        borderWidth: 3,
                        pointBackgroundColor: '#05194d'
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        }
    },

    autoMapHeaders() {
        if (!this.headers || this.headers.length === 0) return;

        this.mappings = { nameCol: '', phoneCol: '', emailCol: '', dateCol: '', courseCol: '', sourceCol: '', statusCol: '' };

        this.headers.forEach(h => {
            const cleanH = String(h).toLowerCase().trim().replace(/[^a-z0-9]/g, '');

            if (!this.mappings.nameCol && (cleanH.includes('name') || cleanH.includes('candidate') || cleanH.includes('student') || cleanH.includes('person'))) {
                this.mappings.nameCol = h;
            }
            if (!this.mappings.phoneCol && (cleanH.includes('phone') || cleanH.includes('mob') || cleanH.includes('mobile') || cleanH.includes('contact') || cleanH.includes('whatsapp') || cleanH.includes('ph'))) {
                this.mappings.phoneCol = h;
            }
            if (!this.mappings.emailCol && (cleanH.includes('email') || cleanH.includes('mail') || cleanH.includes('gmail'))) {
                this.mappings.emailCol = h;
            }
            if (!this.mappings.dateCol && (cleanH.includes('date') || cleanH.includes('reg') || cleanH.includes('created') || cleanH.includes('time'))) {
                this.mappings.dateCol = h;
            }
            if (!this.mappings.courseCol && (cleanH.includes('course') || cleanH.includes('subject') || cleanH.includes('program') || cleanH.includes('stream'))) {
                this.mappings.courseCol = h;
            }
            if (!this.mappings.sourceCol && (cleanH.includes('source') || cleanH.includes('platform') || cleanH.includes('channel') || cleanH.includes('vendor') || cleanH.includes('medium'))) {
                this.mappings.sourceCol = h;
            }
        });

        // Smart status column auto-detection (Prioritize exact 'Final Status')
        const exactFinal = this.headers.find(h => String(h).trim().toLowerCase() === 'final status' || String(h).trim().toLowerCase() === 'final_status');
        if (exactFinal) {
            this.mappings.statusCol = exactFinal;
        } else {
            this.headers.forEach(h => {
                const cleanH = String(h).toLowerCase().trim();
                if (cleanH.includes('fresher') || cleanH.includes('experience') || cleanH.includes('marital') || cleanH.includes('degree') || cleanH.includes('gender')) return;

                if (!this.mappings.statusCol && (cleanH === 'final status' || cleanH === 'final_status' || cleanH === 'final disposition' || cleanH === 'lead status' || cleanH === 'disposition' || cleanH === 'call status' || cleanH === 'status' || cleanH === 'stage')) {
                    this.mappings.statusCol = h;
                }
            });

            if (!this.mappings.statusCol) {
                this.headers.forEach(h => {
                    const cleanH = String(h).toLowerCase().trim();
                    if (cleanH.includes('fresher') || cleanH.includes('experience') || cleanH.includes('marital') || cleanH.includes('degree')) return;

                    if (!this.mappings.statusCol && (cleanH.includes('final') || cleanH.includes('status') || cleanH.includes('disposition') || cleanH.includes('remark') || cleanH.includes('stage'))) {
                        this.mappings.statusCol = h;
                    }
                });
            }
        }

        this.runCleaningEngine();
    },

    categorizeCourse(courseStr) {
        if (!courseStr || !String(courseStr).trim()) return 'Other';
        const courseKey = String(courseStr).trim().toLowerCase();

        const exactMap = {
            'c-da': 'Data Analyst and Scientist',
            'm-da': 'Data Analyst and Scientist',
            'ex-da': 'Data Analyst and Scientist',
            'pyca': 'Data Analyst and Scientist',
            'pbi': 'Data Analyst and Scientist',
            'm-da+vba': 'Data Analyst and Scientist',
            'data analytics': 'Data Analyst and Scientist',
            'data science': 'Data Analyst and Scientist',
            'pbi+asql': 'Data Analyst and Scientist',
            'ad excel': 'Data Analyst and Scientist',
            'ad ex+tally': 'Data Analyst and Scientist',
            'ad ex+pbi': 'Data Analyst and Scientist',
            'python': 'Data Analyst and Scientist',
            'asql': 'Data Analyst and Scientist',
            'mern+ad ex': 'Data Analyst and Scientist',
            'mern+ad excel': 'Data Analyst and Scientist',
            'mso+ad excel': 'Data Analyst and Scientist',
            'mis': 'Data Analyst and Scientist',
            'ad excel+mso': 'Data Analyst and Scientist',
            'mso+m-da': 'Data Analyst and Scientist',
            'cds': 'Data Analyst and Scientist',
            'cgai': 'Data Analyst and Scientist',
            'pcds': 'Data Analyst and Scientist',
            'ai': 'Data Analyst and Scientist',
            'da+cgai': 'Data Analyst and Scientist',
            'mda': 'Data Analyst and Scientist',
            'da+data science': 'Data Analyst and Scientist',
            'ex': 'Data Analyst and Scientist',
            'vda': 'Data Analyst and Scientist',
            'sql & python': 'Data Analyst and Scientist',
            'python+ai': 'Data Analyst and Scientist',
            'python advance': 'Data Analyst and Scientist',
            'ex+sql': 'Data Analyst and Scientist',
            'ex+python': 'Data Analyst and Scientist',
            'computer training for accounting': 'Data Analyst and Scientist',
            'mis+pbi': 'Data Analyst and Scientist',
            'python+ai+ml': 'Data Analyst and Scientist',
            'ex+da+r': 'Data Analyst and Scientist',
            'mso+mis': 'Data Analyst and Scientist',
            'python core': 'Data Analyst and Scientist',
            'sql': 'Data Analyst and Scientist',
            'ai+ml': 'Data Analyst and Scientist',
            'sql+pbi+tableau': 'Data Analyst and Scientist',
            'sql+pbi+tereau': 'Data Analyst and Scientist',
            'ad excel+typing': 'Data Analyst and Scientist',
            'chat gpt': 'Data Analyst and Scientist',
            'mso+cda': 'Data Analyst and Scientist',
            'ad excel+pay roll': 'Data Analyst and Scientist',
            'ot': 'Data Analyst and Scientist',
            'corporate training': 'Data Analyst and Scientist',
            'da+pcds': 'Data Analyst and Scientist',
            'mda+cgai': 'Data Analyst and Scientist',

            'tally gst': 'Accounting and Taxation',
            'gst+itr': 'Accounting and Taxation',
            'cea': 'Accounting and Taxation',
            'cfa-pro': 'Accounting and Taxation',
            'tally': 'Accounting and Taxation',
            'cea-a': 'Accounting and Taxation',
            'tds+gst': 'Accounting and Taxation',
            'itr e-filing': 'Accounting and Taxation',
            'cea-p': 'Accounting and Taxation',
            'gst+tds': 'Accounting and Taxation',
            'umna': 'Accounting and Taxation',
            'gst': 'Accounting and Taxation',
            'accounting': 'Accounting and Taxation',
            'itr': 'Accounting and Taxation',
            'caf': 'Accounting and Taxation',
            'gst e-filing': 'Accounting and Taxation',
            'acaf': 'Accounting and Taxation',
            'mso+tally': 'Accounting and Taxation',
            'mcaf': 'Accounting and Taxation',
            'acct for taxation': 'Accounting and Taxation',
            'sap': 'Accounting and Taxation',
            'busy': 'Accounting and Taxation',
            'tally+busy+gst': 'Accounting and Taxation',
            'acaa': 'Accounting and Taxation',
            'tally gst+e-filing+itr': 'Accounting and Taxation',
            'cfa-p': 'Accounting and Taxation',
            'cea pro': 'Accounting and Taxation',

            'wd+java': 'Full Stack Developer',
            'wd+pyca': 'Full Stack Developer',
            'digital mkt': 'Full Stack Developer',
            'wd': 'Full Stack Developer',
            'full stack+mern': 'Full Stack Developer',
            'wd+php': 'Full Stack Developer',
            'digital marketing': 'Full Stack Developer',
            'c & c++': 'Full Stack Developer',
            'c': 'Full Stack Developer',
            'programming': 'Full Stack Developer',
            'bca': 'Full Stack Developer',
            '.net': 'Full Stack Developer',
            'mern': 'Full Stack Developer',
            'java core+ad': 'Full Stack Developer',
            'c++': 'Full Stack Developer',
            'c++ & java': 'Full Stack Developer',
            'java': 'Full Stack Developer',
            'ncad': 'Full Stack Developer',
            'mcrn': 'Full Stack Developer',
            'react js': 'Full Stack Developer',
            '.net+sql': 'Full Stack Developer',
            'dsa': 'Full Stack Developer',
            'c++ & pyca': 'Full Stack Developer',
            'wd+mern+dsa': 'Full Stack Developer',
            'seo': 'Full Stack Developer',
            'wd+tally': 'Full Stack Developer',
            'wd+node': 'Full Stack Developer',
            'c & java': 'Full Stack Developer',
            'wd+mern': 'Full Stack Developer',
            'wd+python': 'Full Stack Developer',
            'c-da+mso': 'Full Stack Developer',
            'c & c++-python': 'Full Stack Developer',

            'unspecified / direct course': 'Other',
            'computer course': 'Other',
            'ms office': 'Other',
            'mso': 'Other',
            'cd': 'Other',
            'other course': 'Other',
            'ms ofc': 'Other',
            'ccc': 'Other',
            'cti': 'Other',
            'other': 'Other',
            'o level': 'Other',
            'computer typing': 'Other',
            'pgdca': 'Other',
            'coreldraw': 'Other',
            'ppc': 'Other',
            'english': 'Other',
            'mso+gd': 'Other',
            'photoshop': 'Other',
            'ccc+': 'Other',
            'bcc': 'Other',
            'placement': 'Other',
            'mso+wd': 'Other',
            'web': 'Other',
            'computer': 'Other',
            'ecc': 'Other'
        };

        if (exactMap[courseKey]) {
            return exactMap[courseKey];
        }

        if (/tally|gst|itr|tds|accounting|tax|cea|cfa|caf|busy|sap/i.test(courseKey)) {
            return 'Accounting and Taxation';
        }
        if (/da|data|ai|python|pbi|power bi|sql|excel|mis|analytics|science|cgai|vda|cds|mda|chat gpt/i.test(courseKey)) {
            return 'Data Analyst and Scientist';
        }
        if (/wd|web|mern|react|node|java|c\+\+|dsa|developer|programming|bca|full stack|digital marketing|seo|\.net/i.test(courseKey)) {
            return 'Full Stack Developer';
        }

        return 'Other';
    },

    parseRealDateAndMonth(val) {
        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        const monthShorts = ["jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec"];

        if (!val || String(val).trim() === '' || String(val).trim().toUpperCase() === 'N/A') {
            return { date: '', month: '', year: '', quarter: '' };
        }

        let str = String(val).trim();
        let day = 0, monthNum = 0, yearNum = 0;

        // 1. Check Excel Serial Number (e.g. 45480)
        let num = parseFloat(str);
        if (!isNaN(num) && num > 10000 && num < 100000) {
            let utc_days = Math.floor(num - 25569);
            let utc_value = utc_days * 86400;
            let date_info = new Date(utc_value * 1000);
            day = date_info.getUTCDate();
            monthNum = date_info.getUTCMonth() + 1;
            yearNum = date_info.getUTCFullYear();
        } 
        // 2. Check numeric pattern yyyy-mm-dd
        else if (/^\d{4}[\/\-\.]\d{1,2}[\/\-\.]\d{1,2}$/.test(str)) {
            let p = str.split(/[\/\-\.]/);
            yearNum = parseInt(p[0], 10);
            monthNum = parseInt(p[1], 10);
            day = parseInt(p[2], 10);
        } 
        // 3. Check numeric pattern dd/mm/yyyy or mm/dd/yyyy
        else if (/^\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4}$/.test(str)) {
            let p = str.split(/[\/\-\.]/);
            let a = parseInt(p[0], 10);
            let b = parseInt(p[1], 10);
            let y = parseInt(p[2], 10);
            if (y < 100) y += 2000;

            if (a > 12 && b <= 12) { day = a; monthNum = b; yearNum = y; }
            else if (b > 12 && a <= 12) { monthNum = a; day = b; yearNum = y; }
            else { day = a; monthNum = b; yearNum = y; }
        } 
        // 4. Check textual month pattern e.g. 31-Jan-2025 or 31-Jul-2025 or 31 Jan 2025
        else if (/[a-zA-Z]/.test(str)) {
            let matches = str.match(/(\d{1,2})[\/\-\.\s]+([a-zA-Z]+)[\/\-\.\s]+(\d{2,4})/);
            if (!matches) {
                matches = str.match(/([a-zA-Z]+)[\/\-\.\s]+(\d{1,2})[\/\-\.\s,]+(\d{2,4})/);
                if (matches) {
                    let mStr = matches[1].toLowerCase().slice(0, 3);
                    let mIdx = monthShorts.indexOf(mStr);
                    if (mIdx !== -1) monthNum = mIdx + 1;
                    day = parseInt(matches[2], 10);
                    yearNum = parseInt(matches[3], 10);
                    if (yearNum < 100) yearNum += 2000;
                }
            } else {
                day = parseInt(matches[1], 10);
                let mStr = matches[2].toLowerCase().slice(0, 3);
                let mIdx = monthShorts.indexOf(mStr);
                if (mIdx !== -1) monthNum = mIdx + 1;
                yearNum = parseInt(matches[3], 10);
                if (yearNum < 100) yearNum += 2000;
            }

            if (!monthNum) {
                let parsedDate = new Date(str);
                if (!isNaN(parsedDate.getTime())) {
                    day = parsedDate.getDate();
                    monthNum = parsedDate.getMonth() + 1;
                    yearNum = parsedDate.getFullYear();
                }
            }
        }

        let formattedDate = str;
        let monthName = '';
        let yearStr = '';
        let quarterStr = '';

        if (monthNum >= 1 && monthNum <= 12 && yearNum > 1990 && yearNum < 2050) {
            monthName = monthNames[monthNum - 1];
            yearStr = String(yearNum);
            formattedDate = `${day ? String(day).padStart(2,'0') + '-' : ''}${monthName.slice(0,3)}-${yearStr}`;

            if (monthNum >= 1 && monthNum <= 3) quarterStr = 'Q1';
            else if (monthNum >= 4 && monthNum <= 6) quarterStr = 'Q2';
            else if (monthNum >= 7 && monthNum <= 9) quarterStr = 'Q3';
            else if (monthNum >= 10 && monthNum <= 12) quarterStr = 'Q4';
        }

        return { date: formattedDate, month: monthName, year: yearStr, quarter: quarterStr };
    },

    validatePhoneJS(rawPhone) {
        if (!rawPhone || !String(rawPhone).trim() || String(rawPhone).trim().toUpperCase() === 'N/A') {
            return { isValid: false, reason: 'Blank Phone', digits: '' };
        }
        let str = String(rawPhone).trim();
        let digits = str.replace(/\D/g, '');

        if (!digits) return { isValid: false, reason: 'Blank Phone', digits: '' };

        // Extract 10 digits from right side if length >= 10
        if (digits.length >= 10) {
            let rightmost10 = digits.slice(-10);
            return { isValid: true, reason: '', digits: rightmost10 };
        }

        // Support Nepal & International numbers (7 to 9 digits)
        if (digits.length >= 7) {
            return { isValid: true, reason: '', digits: digits };
        }

        return { isValid: false, reason: 'Short Phone Number', digits: digits };
    },

    validateEmailJS(rawEmail) {
        if (!rawEmail || !String(rawEmail).trim()) return { isValid: false, reason: 'Blank Email', email: '' };
        let email = String(rawEmail).trim().toLowerCase().replace(/\s+/g, '');

        const junk = ['0', '-', '--', 'n/a', 'na', 'null', 'undefined', 'none', 'blank', 'space', 'c', 'cti', 'computer course', 'bca'];
        if (junk.includes(email) || !email.includes('@')) {
            return { isValid: false, reason: 'Invalid Email Syntax', email: '' };
        }

        // Domain typos auto-correction
        email = email.replace(/@gamill?\.com$/i, '@gmail.com')
                     .replace(/@gamil\.com$/i, '@gmail.com')
                     .replace(/@gmial\.com$/i, '@gmail.com')
                     .replace(/@gmaill?\.com$/i, '@gmail.com')
                     .replace(/@gmai\.com$/i, '@gmail.com')
                     .replace(/@hotmaill?\.com$/i, '@hotmail.com')
                     .replace(/@yahooo?\.com$/i, '@yahoo.com');

        const dummy = ['test@test.com','noemail@gmail.com','na@gmail.com','none@gmail.com','null@gmail.com','abc@xyz.com','no@email.com','email@gmail.com','xyz@gmail.com','dummy@gmail.com','sample@gmail.com','user@gmail.com'];
        if (dummy.includes(email)) return { isValid: false, reason: 'Dummy Email', email: '' };

        if (/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i.test(email)) {
            return { isValid: true, reason: '', email: email };
        }

        return { isValid: false, reason: 'Invalid Email Syntax', email: '' };
    },

    getFlexibleRowValue(row, targetKeywords, primaryCol = '') {
        if (primaryCol && row[primaryCol] !== undefined && row[primaryCol] !== null) {
            const val = String(row[primaryCol]).trim();
            if (val && val.toUpperCase() !== 'N/A' && val.toUpperCase() !== 'NULL' && val.toUpperCase() !== 'UNDEFINED') {
                return val;
            }
        }

        const rowKeys = Object.keys(row);
        for (let key of rowKeys) {
            if (key === '_sheet_name') continue;
            const cleanKey = String(key).toLowerCase().trim().replace(/[^a-z0-9]/g, '');

            for (let kw of targetKeywords) {
                if (cleanKey.includes(kw)) {
                    const val = String(row[key] || '').trim();
                    if (val && val.toUpperCase() !== 'N/A' && val.toUpperCase() !== 'NULL' && val.toUpperCase() !== 'UNDEFINED') {
                        return val;
                    }
                }
            }
        }
        return '';
    },

    exportUniqueCoursesReport() {
        let dataset = (this.dbLeads && this.dbLeads.length > 0) ? this.dbLeads : this.processedData;

        if (!dataset || dataset.length === 0) {
            return alert('No dataset available to export unique courses!');
        }

        const courseGroups = {};

        dataset.forEach(r => {
            let course = String(r.Raw_Course || r.raw_course || '').trim();
            if (!course || course.toUpperCase() === 'N/A' || course.toUpperCase() === 'NULL') {
                course = 'Unspecified / Direct Course';
            }

            let cat = r.Major_Category || r.major_category || this.categorizeCourse(course);
            let status = String(r.Status || r.status || '').toLowerCase();

            if (!courseGroups[course]) {
                courseGroups[course] = {
                    course: course,
                    category: cat,
                    total: 0,
                    enrolled: 0,
                    visited: 0,
                    activeFollowup: 0
                };
            }

            courseGroups[course].total++;
            if (status.includes('enrol') || status.includes('confirm')) {
                courseGroups[course].enrolled++;
            }
            if (status.includes('visit')) {
                courseGroups[course].visited++;
            }
            if (status.includes('will') || status.includes('call') || status.includes('expected') || status.includes('next')) {
                courseGroups[course].activeFollowup++;
            }
        });

        const exportRows = Object.values(courseGroups).map((item, idx) => ({
            '#': idx + 1,
            'Unique Course Name': item.course,
            'Major Category': item.category,
            'Total Inflow Leads': item.total,
            'Enrolled & Confirmed': item.enrolled,
            'Campus Visited': item.visited,
            'Active Follow-ups': item.activeFollowup
        }));

        const ws = XLSX.utils.json_to_sheet(exportRows);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Unique_Courses_Report");
        XLSX.writeFile(wb, "Unique_Courses_Report.xlsx");
    },

    cleanNameJS(rawName) {
        if (!rawName || !String(rawName).trim()) return '';
        let str = String(rawName).trim();

        // Hindi Devanagari Transliteration Map
        const hindiMap = {
            'क्ष': 'ksh', 'त्र': 'tr', 'ज्ञ': 'gya', 'श्र': 'shr',
            'क': 'k', 'ख': 'kh', 'ग': 'g', 'घ': 'gh', 'ङ': 'n',
            'च': 'ch', 'छ': 'chh', 'ज': 'j', 'झ': 'jh', 'ञ': 'n',
            'ट': 't', 'ठ': 'th', 'ड': 'd', 'ढ': 'dh', 'ण': 'n',
            'त': 't', 'थ': 'th', 'द': 'd', 'ध': 'dh', 'न': 'n',
            'प': 'p', 'फ': 'f', 'ब': 'b', 'भ': 'bh', 'म': 'm',
            'य': 'y', 'र': 'r', 'ल': 'l', 'व': 'v', 'श': 'sh', 'ष': 'sh', 'स': 's', 'ह': 'h',
            'ड़': 'd', 'ढ़': 'dh',
            'अ': 'a', 'आ': 'aa', 'इ': 'i', 'ई': 'ee', 'उ': 'u', 'ऊ': 'oo', 'ऋ': 'ri', 'ए': 'e', 'ऐ': 'ai', 'ओ': 'o', 'औ': 'au',
            'ा': 'a', 'ि': 'i', 'ी': 'ee', 'ु': 'u', 'ू': 'oo', 'ृ': 'ri', 'े': 'e', 'ै': 'ai', 'ो': 'o', 'ौ': 'au', 'ं': 'n', 'ः': 'h', '्': ''
        };

        if (/[\u0900-\u097F]/.test(str)) {
            str = str.replace(/[\u0900-\u097F]/g, char => hindiMap[char] || '');
        }

        // Remove special chars, numbers, symbols - keep A-Z, a-z and spaces only!
        let cleaned = str.replace(/[^a-zA-Z\s]/g, ' ').replace(/\s+/g, ' ').trim();
        if (!cleaned || cleaned.length < 2) return '';

        // Title Case / Sentence Case conversion
        return cleaned.toLowerCase().split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
    },

    runCleaningEngine() {
        if (!this.rawDataset || this.rawDataset.length === 0) return;

        let result = [];
        let counter = 1;
        let seenPhones = new Set();
        let seenEmails = new Set();

        this.rawDataset.forEach(row => {
            let nameVal = this.cleanNameJS(this.getFlexibleRowValue(row, ['name', 'candidate', 'student', 'person'], this.mappings.nameCol));
            let rawPhoneVal = this.getFlexibleRowValue(row, ['phone', 'mob', 'mobile', 'contact', 'whatsapp', 'ph'], this.mappings.phoneCol);
            let rawEmailVal = this.getFlexibleRowValue(row, ['email', 'mail', 'gmail'], this.mappings.emailCol);
            let courseVal = this.getFlexibleRowValue(row, ['course', 'subject', 'program', 'stream'], this.mappings.courseCol);

            // Skip completely empty Excel placeholder rows (where Name, Phone, Email & Course are ALL N/A or Blank)
            const isBlank = (v) => !v || v.toUpperCase() === 'N/A' || v.toUpperCase() === 'NULL' || v.toUpperCase() === 'UNDEFINED';
            if (isBlank(nameVal) && isBlank(String(rawPhoneVal || '').trim()) && isBlank(String(rawEmailVal || '').trim()) && isBlank(courseVal)) {
                return;
            }

            let phoneRes = this.validatePhoneJS(rawPhoneVal);
            let emailRes = this.validateEmailJS(rawEmailVal);

            let phoneVal = phoneRes.isValid ? phoneRes.digits : '';
            let emailVal = emailRes.email;

            // Allow import if AT LEAST ONE contact method is valid (Phone OR Email)!
            let isInvalidContact = !phoneRes.isValid && !emailRes.isValid;
            let invalidReason = '';
            if (isInvalidContact) {
                invalidReason = 'Both Phone & Email Missing/Invalid';
            } else if (phoneRes.isValid && emailRes.isValid) {
                invalidReason = 'Valid Authentic Lead';
            } else if (phoneRes.isValid) {
                invalidReason = 'Valid Phone (Email Blank)';
            } else if (emailRes.isValid) {
                invalidReason = 'Valid Email (Phone Blank)';
            }

            let sourceVal = this.getFlexibleRowValue(row, ['source', 'platform', 'channel', 'vendor', 'medium'], this.mappings.sourceCol) || 'Direct/Organic';
            let dateVal = this.getFlexibleRowValue(row, ['date', 'reg', 'created', 'time'], this.mappings.dateCol);

            // Read EXACT Final Status string from sheet cell
            let statusVal = '';
            const rowKeys = Object.keys(row);
            const exactFinalKey = rowKeys.find(k => String(k).trim().toLowerCase() === 'final status' || String(k).trim().toLowerCase() === 'final_status');
            
            if (exactFinalKey && row[exactFinalKey] !== undefined && row[exactFinalKey] !== null) {
                statusVal = String(row[exactFinalKey]).trim();
            } else {
                statusVal = this.getFlexibleRowValue(row, ['final status', 'final_status', 'disposition', 'status', 'remark', 'stage'], this.mappings.statusCol);
            }

            if (!sourceVal) sourceVal = 'Direct/Organic';
            let categoryVal = this.categorizeCourse(courseVal);
            let parsedDateObj = this.parseRealDateAndMonth(dateVal);

            // Strict Deduplication by Phone OR Email
            let isDup = false;
            if (phoneVal && seenPhones.has(phoneVal)) {
                isDup = true;
            } else if (emailVal && seenEmails.has(emailVal)) {
                isDup = true;
            }

            if (phoneVal) seenPhones.add(phoneVal);
            if (emailVal) seenEmails.add(emailVal);

            result.push({
                _id: counter++,
                sheet_name: row['_sheet_name'] || 'Sheet1',
                Date: parsedDateObj.date,
                Month: parsedDateObj.month,
                Year: parsedDateObj.year,
                Quarter: parsedDateObj.quarter,
                Name: nameVal,
                Mob: phoneVal,
                Email: emailVal,
                Raw_Course: courseVal,
                Major_Category: categoryVal,
                Source: sourceVal,
                Status: statusVal,
                is_duplicate: isDup,
                is_invalid_contact: isInvalidContact,
                invalid_reason: invalidReason
            });
        });

        this.processedData = result;
        this.page = 1;
        this.saveToDatabaseVault(true);
        this.renderCharts();
    },

    saveToDatabaseVault(silent = false) {
        const cleanAuthenticLeads = this.processedData.filter(r => !r.is_duplicate && !r.is_invalid_contact);
        fetch('/api/save-database', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                file_name: this.fileName || 'Imported_Workbook.xlsx',
                sheet_count: 1,
                leads: cleanAuthenticLeads,
                skip_duplicates: true
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                if (!silent) alert(res.message);
                this.loadDatabaseVault();
                this.loadAnalyticsSummary();
                this.renderCharts();
            }
        });
    },

    handleFileUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

        this.fileName = file.name;
        const reader = new FileReader();
        reader.onload = (evt) => {
            const wb = XLSX.read(evt.target.result, { type: 'binary' });

            // COMPILE ALL SHEETS IN WORKBOOK
            let compiled = [];
            wb.SheetNames.forEach(sheetName => {
                const ws = wb.Sheets[sheetName];
                const sheetRows = XLSX.utils.sheet_to_json(ws, { defval: '', raw: false });
                sheetRows.forEach(r => {
                    r['_sheet_name'] = sheetName;
                    compiled.push(r);
                });
            });

            this.rawDataset = compiled;
            if (compiled.length > 0) {
                let headerSet = new Set();
                compiled.slice(0, 50).forEach(r => {
                    Object.keys(r).forEach(k => {
                        if (k !== '_sheet_name') headerSet.add(k);
                    });
                });
                this.headers = Array.from(headerSet);
                this.autoMapHeaders();
            }
        };
        reader.readAsBinaryString(file);
    },

    get totalPages() {
        return Math.ceil(this.processedData.length / (this.pageSize || 50)) || 1;
    },

    get paginatedRows() {
        let start = (this.page - 1) * this.pageSize;
        return this.processedData.slice(start, start + this.pageSize);
    },

    openExportModal(target) {
        this.exportTarget = target;
        this.exportModalOpen = true;
    },

    triggerStreamCsvExport() {
        window.location.href = `/export/csv?status=${encodeURIComponent(this.exportStatusFilter)}&source=${encodeURIComponent(this.exportSourceFilter)}`;
        this.exportModalOpen = false;
    },

    triggerDynamicExport() {
        let dataset = (this.exportTarget === 'vault') ? this.dbLeads : this.processedData;

        if (!dataset || dataset.length === 0) return alert('No data available to export!');

        // Filter dataset based on category, source, status, and exclusion rules
        let filtered = dataset.filter(r => {
            const phone  = String(r.Mob || r.mob || '').trim();
            const email  = String(r.Email || r.email || '').trim();
            const status = String(r.Status || r.status || '').trim().toLowerCase();
            const source = String(r.Source || r.source || '').trim();
            const cat    = String(r.Major_Category || r.major_category || '').trim();

            // Rule 1: If BOTH Phone and Email are missing/blank/unauthorized, ignore row completely!
            if (!phone && !email) {
                return false;
            }

            // Rule 2: Single Column Export Strictness:
            // If exporting Phone ONLY (without Email), ignore rows missing Phone
            if (this.exportCols.mob && !this.exportCols.email && !phone) {
                return false;
            }

            // If exporting Email ONLY (without Phone), ignore rows missing Email
            if (this.exportCols.email && !this.exportCols.mob && !email) {
                return false;
            }

            // Exclude Blank Phone or Email if explicit checkbox is ticked
            if (this.exportSkipBlankContact && (!phone || !email)) {
                return false;
            }

            // Exclude Enrolled Leads
            if (this.exportIgnoreEnrolled && status.includes('enrolled')) {
                return false;
            }

            // Exclude No Need To Call
            if (this.exportIgnoreNoNeedToCall && (status.includes('no need') || status.includes('no need to call'))) {
                return false;
            }

            // Exclude Student Data / Not Interested
            if (this.exportIgnoreStudentData && (status.includes('student') || status.includes('not interested') || status.includes('student data'))) {
                return false;
            }

            // Category Filter
            if (this.exportCategoryFilter !== 'ALL' && cat !== this.exportCategoryFilter) {
                return false;
            }

            // Status Filter
            if (this.exportStatusFilter !== 'ALL' && !status.includes(this.exportStatusFilter.toLowerCase())) {
                return false;
            }

            // Source Filter
            if (this.exportSourceFilter !== 'ALL' && !source.toLowerCase().includes(this.exportSourceFilter.toLowerCase())) {
                return false;
            }

            return true;
        });

        if (filtered.length === 0) return alert('No records matched your custom export filters!');

        // Build Custom Column Rows
        const exportRows = filtered.map((r, i) => {
            let item = { '#': i + 1 };
            if (this.exportCols.date) item['Date'] = r.Date || r.date || '';
            if (this.exportCols.year) {
                item['Month'] = r.Month || r.month || '';
                item['Year'] = r.Year || r.year || '';
                item['Quarter'] = r.Quarter || r.quarter || '';
            }
            if (this.exportCols.name) item['Student Name'] = r.Name || r.name || '';
            if (this.exportCols.mob) item['Mobile No'] = r.Mob || r.mob || '';
            if (this.exportCols.email) item['Email Address'] = r.Email || r.email || '';
            if (this.exportCols.course) item['Course Name'] = r.Raw_Course || r.raw_course || '';
            if (this.exportCols.category) item['Major Category'] = r.Major_Category || r.major_category || '';
            if (this.exportCols.source) item['Lead Source'] = r.Source || r.source || '';
            if (this.exportCols.status) item['Final Status'] = r.Status || r.status || '';

            return item;
        });

        const ws = XLSX.utils.json_to_sheet(exportRows);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Clean_Leads_Export");

        const filename = `Clean_Leads_Export_${new Date().toISOString().slice(0,10)}.xlsx`;
        XLSX.writeFile(wb, filename, { bookType: 'xlsx' });

        this.exportModalOpen = false;
    },

    searchStudentByEmail() {
        if (!this.studentSearchEmail) return alert('Please enter an email address to search!');
        fetch(`/api/leads/search-email?email=${encodeURIComponent(this.studentSearchEmail)}`)
            .then(r => r.json())
            .then(res => {
                if (res.success) this.searchedStudentResults = res.leads;
                else this.searchedStudentResults = [];
            });
    },

    deleteSingleStudentSearched(id, email) {
        if (!confirm(`Are you sure you want to delete the student record for: ${email}?`)) return;
        fetch(`/api/leads/${id}`, { method: 'DELETE' })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    alert('Student record deleted!');
                    this.searchStudentByEmail();
                    this.loadDatabaseVault();
                    this.loadAnalyticsSummary();
                }
            });
    },

    openEditLeadModal(lead) {
        this.editLeadForm = {
            id: lead.id,
            name: lead.Name || lead.name || '',
            mob: lead.Mob || lead.mob || '',
            email: lead.Email || lead.email || '',
            raw_course: lead.Raw_Course || lead.raw_course || '',
            source: lead.Source || lead.source || 'Direct/Organic',
            status: lead.Status || lead.status || '',
            date: lead.Date || lead.date || ''
        };
        this.editLeadModalOpen = true;
    },

    deleteSingleLead(id) {
        if (!confirm('Are you sure you want to delete this lead?')) return;
        fetch(`/api/leads/${id}`, { method: 'DELETE' })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                this.loadDatabaseVault();
                this.loadAnalyticsSummary();
                this.renderCharts();
            }
        });
    },

    wipeAllDatabaseLeads() {
        const password = prompt("🔐 SECURITY CONFIRMATION REQUIRED:\n\nPlease enter the Admin Password to confirm wiping all database records:");
        if (!password) return;

        fetch('/api/leads/wipe-all', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password: password })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                alert(res.message || '🧹 Database Vault wiped!');
                this.loadDatabaseVault();
                this.loadAnalyticsSummary();
                this.renderCharts();
            } else {
                alert(res.error || '🔒 Password Authentication Failed!');
            }
        });
    },

    loadAnalyticsSummary() {
        fetch('/api/analytics-summary')
            .then(r => r.json())
            .then(res => {
                if (res.success) this.analyticsMetrics = res.metrics;
            });
    },

    loadDatabaseVault() {
        fetch('/api/database-leads')
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    this.dbLeads = (res.leads || []).map(r => {
                        let parsed = this.parseRealDateAndMonth(r.date || r.Date);
                        if (parsed.month) r.Month = r.month = parsed.month;
                        if (parsed.year) r.Year = r.year = parsed.year;
                        if (parsed.quarter) r.Quarter = r.quarter = parsed.quarter;
                        return r;
                    });
                    this.renderCharts();
                }
            });
    },

    saveToDatabaseVault(silent = false) {
        const uniqueLeads = this.processedData.filter(r => !r.is_duplicate);
        fetch('/api/save-database', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                file_name: this.fileName || 'Imported_Workbook.xlsx',
                sheet_count: 1,
                leads: uniqueLeads,
                skip_duplicates: true
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                if (!silent) alert(res.message);
                this.loadDatabaseVault();
                this.loadAnalyticsSummary();
                this.renderCharts();
            }
        });
    },

    get filteredDbLeads() {
        return this.dbLeads.filter(r => {
            const cat = r.Major_Category || r.major_category;
            const src = String(r.Source || r.source || '').trim();
            const yr  = String(r.Year || r.year || '');
            const qtr = String(r.Quarter || r.quarter || '');
            const st  = String(r.Status || r.status || '').trim();

            if (this.vaultCategoryFilter !== 'ALL' && cat !== this.vaultCategoryFilter) return false;
            if (this.vaultSourceFilter !== 'ALL' && !src.toLowerCase().includes(this.vaultSourceFilter.toLowerCase())) return false;
            if (this.vaultStatusFilter !== 'ALL' && !st.toLowerCase().includes(this.vaultStatusFilter.toLowerCase())) return false;
            if (this.vaultYearFilter !== 'ALL' && yr !== this.vaultYearFilter) return false;
            if (this.vaultQuarterFilter !== 'ALL' && qtr !== this.vaultQuarterFilter) return false;

            return true;
        });
    }
};

document.addEventListener('alpine:init', () => {
    Alpine.data('cleanerApp', () => cleanerDataDefinition);
});

function cleanerApp() {
    return cleanerDataDefinition;
}
</script>
@endsection
