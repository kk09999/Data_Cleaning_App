@extends('layouts.app')

@section('content')
<div x-data="cleanerApp()" x-init="initData()">

    <!-- Navigation Tabs & Dynamic Export Modal Trigger -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; gap: 12px;">
            <button 
                class="btn" 
                :class="activeTab === 'cleaner' ? 'btn-primary' : 'btn-outline'"
                @click="activeTab = 'cleaner'"
            >
                <i data-lucide="wand-2" style="width:18px"></i> Multi-Sheet Importer & Cleaner
            </button>
            <button 
                class="btn" 
                :class="activeTab === 'vault' ? 'btn-primary' : 'btn-outline'"
                @click="activeTab = 'vault'; loadDatabaseVault()"
            >
                <i data-lucide="database" style="width:18px"></i> MySQL Database Vault (<span x-text="dbLeads.length"></span> Records)
            </button>
        </div>

        <button class="btn btn-gold" @click="openExportModal(activeTab === 'vault' ? 'vault' : 'current')">
            <i data-lucide="sliders" style="width:18px"></i> 🎯 Dynamic Custom Export (Filters & Source)
        </button>
    </div>

    <!-- TAB 1: Cleaner & Multi-Sheet Aggregator -->
    <div x-show="activeTab === 'cleaner'">

        <!-- Hero Banner -->
        <div style="background: var(--gradient-primary); border-radius: 14px; padding: 25px 35px; color: white; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(8,42,125,0.18); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div>
                <h1 style="font-size: 24px; font-weight: 700; margin-bottom: 6px;">Multi-Sheet Importer, Lead Source & MySQL Engine</h1>
                <p style="color: #cbd5e1; font-size: 13.5px;">
                    • Filter by <strong>Lead Source</strong> (Google, Facebook, Instagram, Website, etc.).<br>
                    • <strong>Strict Database Deduplication</strong>: Multi-uploading same files rejects duplicate phone numbers automatically!<br>
                    • Excel Export Rule: <strong>Automatically avoids rows with blank Phone or Email</strong>!
                </p>
            </div>
            <button class="btn btn-gold" @click="loadSampleDataset()">
                <i data-lucide="sparkles" style="width:18px"></i> Try Demo Dataset
            </button>
        </div>

        <!-- Step 1: File Importer -->
        <div class="card-panel">
            <div class="card-title">
                <i data-lucide="upload" style="width:22px; color:var(--primary-navy)"></i> Step 1: Import Workbooks (Compiles ALL Sheets)
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div 
                    :style="importType === 'file' ? 'border-color: var(--accent-blue); background:#f0f7ff;' : ''"
                    style="border: 2px dashed #cbd5e1; border-radius: 12px; padding: 22px; text-align: center; background: #f8fafc; cursor: pointer;"
                    @click="importType = 'file'"
                >
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: #e0edff; color: var(--primary-navy); display: flex; align-items: center; justify-content: center; margin: 0 auto 10px auto;">
                        <i data-lucide="file-spreadsheet" style="width:22px"></i>
                    </div>
                    <h3 style="font-size: 15px; font-weight: 600; margin-bottom: 4px;">Excel (All Sheets) / CSV</h3>
                    <p style="font-size: 12px; color: #64748b;">Reads and compiles all sheets inside workbook</p>
                </div>

                <div 
                    :style="importType === 'google' ? 'border-color: var(--accent-blue); background:#f0f7ff;' : ''"
                    style="border: 2px dashed #cbd5e1; border-radius: 12px; padding: 22px; text-align: center; background: #f8fafc; cursor: pointer;"
                    @click="importType = 'google'"
                >
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: #e0edff; color: var(--primary-navy); display: flex; align-items: center; justify-content: center; margin: 0 auto 10px auto;">
                        <i data-lucide="link" style="width:22px"></i>
                    </div>
                    <h3 style="font-size: 15px; font-weight: 600; margin-bottom: 4px;">Google Sheet URL</h3>
                    <p style="font-size: 12px; color: #64748b;">Paste public shareable Google Sheet link</p>
                </div>

                <div 
                    style="border: 2px dashed #fef08a; border-radius: 12px; padding: 22px; text-align: center; background: #fefce8; cursor: pointer;"
                    @click="loadSampleDataset()"
                >
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: #fef08a; color: #854d0e; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px auto;">
                        <i data-lucide="sparkles" style="width:22px"></i>
                    </div>
                    <h3 style="font-size: 15px; font-weight: 600; margin-bottom: 4px; color: #854d0e;">Try Demo Dataset</h3>
                    <p style="font-size: 12px; color: #a16207;">Test Lead Source & Deduplication</p>
                </div>
            </div>

            <!-- File Upload Action -->
            <template x-if="importType === 'file'">
                <div style="padding: 20px; background: #f8fafc; border-radius: 10px; border: 1px solid var(--border-color); text-align: center;">
                    <label style="display: inline-block; cursor: pointer;">
                        <span class="btn btn-primary" style="padding: 12px 28px; font-size: 15px;">
                            <i data-lucide="upload" style="width:18px"></i> Choose Excel File (.xlsx, .xls, .csv)
                        </span>
                        <input type="file" accept=".xlsx, .xls, .csv" @change="handleFileUpload($event)" style="display: none;">
                    </label>
                    <p style="font-size: 12px; color: #64748b; margin-top: 10px;">
                        Auto-detects Mobile, Email, Name, Date, Course & Lead Source columns
                    </p>
                </div>
            </template>

            <!-- Google Sheet Action -->
            <template x-if="importType === 'google'">
                <div style="padding: 20px; background: #f8fafc; border-radius: 10px; border: 1px solid var(--border-color);">
                    <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">
                        Google Sheet Public Share Link:
                    </label>
                    <div style="display: flex; gap: 10px;">
                        <input type="url" placeholder="https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit" x-model="googleUrl" style="flex: 1; padding: 10px 16px; border-radius: 50px; border: 1px solid var(--border-color); font-size: 14px; outline: none;">
                        <button class="btn btn-primary" @click="fetchGoogleSheet()" :disabled="loading">
                            <span x-text="loading ? 'Fetching...' : 'Fetch Sheet'"></span>
                        </button>
                    </div>
                </div>
            </template>

            <template x-if="successMsg">
                <div style="margin-top: 15px; padding: 12px 16px; background: #f0fdf4; border-left: 4px solid #22c55e; color: #166534; border-radius: 6px; font-size: 13px;">
                    <span x-text="successMsg"></span>
                </div>
            </template>
        </div>

        <!-- Step 2: Column Binding -->
        <template x-if="headers.length > 0">
            <div class="card-panel">
                <div class="card-title">
                    <i data-lucide="settings-2" style="width:22px; color:var(--primary-navy)"></i> Step 2: Auto-Detected Lead Column Bindings
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 18px; margin-bottom: 20px;">
                    <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid var(--border-color);">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: var(--primary-navy); margin-bottom: 6px;">
                            📅 Date Column
                        </label>
                        <select x-model="mappings.dateCol" @change="runCleaningEngine()" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px;">
                            <option value="">-- Select Column --</option>
                            <template x-for="h in headers" :key="h">
                                <option :value="h" x-text="h"></option>
                            </template>
                        </select>
                    </div>

                    <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid var(--border-color);">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: var(--primary-navy); margin-bottom: 6px;">
                            👤 Name Column (English Only)
                        </label>
                        <select x-model="mappings.nameCol" @change="runCleaningEngine()" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px;">
                            <option value="">-- Select Column --</option>
                            <template x-for="h in headers" :key="h">
                                <option :value="h" x-text="h"></option>
                            </template>
                        </select>
                    </div>

                    <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid var(--border-color);">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: var(--primary-navy); margin-bottom: 6px;">
                            📞 Mob / Phone Column (Validated)
                        </label>
                        <select x-model="mappings.phoneCol" @change="runCleaningEngine()" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px; font-weight:700; color:#0d6efd">
                            <option value="">-- Select Column --</option>
                            <template x-for="h in headers" :key="h">
                                <option :value="h" x-text="h"></option>
                            </template>
                        </select>
                    </div>

                    <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid var(--border-color);">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: var(--primary-navy); margin-bottom: 6px;">
                            ✉️ Email Column
                        </label>
                        <select x-model="mappings.emailCol" @change="runCleaningEngine()" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px; font-weight:700; color:#0d6efd">
                            <option value="">-- Select Column --</option>
                            <template x-for="h in headers" :key="h">
                                <option :value="h" x-text="h"></option>
                            </template>
                        </select>
                    </div>

                    <div style="background: #fefce8; padding: 15px; border-radius: 8px; border: 1px solid #fef08a;">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: #854d0e; margin-bottom: 6px;">
                            🎓 Course Column
                        </label>
                        <select x-model="mappings.courseCol" @change="runCleaningEngine()" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #fde047; font-size: 13px; font-weight: 600;">
                            <option value="">-- Select Column --</option>
                            <template x-for="h in headers" :key="h">
                                <option :value="h" x-text="h"></option>
                            </template>
                        </select>
                    </div>

                    <div style="background: #f0fdf4; padding: 15px; border-radius: 8px; border: 1px solid #bbf7d0;">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: #166534; margin-bottom: 6px;">
                            🌐 Source Column (Google, FB, etc.)
                        </label>
                        <select x-model="mappings.sourceCol" @change="runCleaningEngine()" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #86efac; font-size: 13px; font-weight: 600;">
                            <option value="">-- Select Column --</option>
                            <template x-for="h in headers" :key="h">
                                <option :value="h" x-text="h"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button class="btn btn-gold" @click="runCleaningEngine()">
                        Re-Run Cleaning Engine &rarr;
                    </button>
                </div>
            </div>
        </template>

        <!-- Step 3: Overview & Dynamic Custom Export Hub -->
        <template x-if="processedData.length > 0">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap:wrap; gap:10px;">
                    <h2 style="font-size: 18px; font-weight: 700; color: var(--primary-navy);">
                        Step 3: Categorization & Database Save Hub
                    </h2>
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap:wrap;">
                        <button class="btn btn-primary" @click="saveToDatabaseVault()">
                            <i data-lucide="database-backup" style="width:18px"></i> Save Unique Leads to MySQL
                        </button>
                        <button class="btn btn-gold" @click="openExportModal('current')">
                            <i data-lucide="sliders" style="width:18px"></i> Dynamic Custom Export Hub
                        </button>
                    </div>
                </div>

                <!-- Phone Duplicate Stats Card -->
                <div style="background: #fff7ed; border-left: 5px solid #f97316; border-radius: 10px; padding: 16px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <strong style="color: #c2410c; font-size: 15px; display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="shield-check" style="width:18px"></i> Phone & Database Validation Summary
                        </strong>
                        <p style="font-size: 13px; color: #7c2d12; margin-top: 2px;">
                            Total Processed: <strong><span x-text="processedData.length"></span></strong> | 
                            Unique Leads: <strong><span x-text="processedData.filter(r => !r.is_duplicate).length"></span></strong> | 
                            Duplicates Rejected: <strong style="color:#ef4444"><span x-text="processedData.filter(r => r.is_duplicate).length"></span></strong>
                        </p>
                    </div>

                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer; color: #7c2d12;">
                        <input type="checkbox" x-model="hideDuplicates" style="width: 16px; height: 16px;">
                        Hide Duplicates from Display
                    </label>
                </div>

                <!-- Filters Panel: Source & Category Filters -->
                <div style="background: white; border-radius: 12px; padding: 18px 22px; border: 1px solid var(--border-color); margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 4px;">
                                🌐 Filter by Lead Source:
                            </label>
                            <select x-model="sourceFilter" style="padding: 8px 14px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px; font-weight: 600;">
                                <option value="ALL">All Sources (Google, FB, Insta, etc.)</option>
                                <template x-for="s in uniqueSources" :key="s">
                                    <option :value="s" x-text="s"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 4px;">
                                🎓 Filter by Category:
                            </label>
                            <select x-model="categoryFilter" style="padding: 8px 14px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px; font-weight: 600;">
                                <option value="ALL">All Categories</option>
                                <option value="Data Analyst and Scientist">Data Analyst & Scientist</option>
                                <option value="Accounting and Taxation">Accounting & Taxation</option>
                                <option value="Full Stack Developer">Full Stack Developer</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div style="font-size: 13px; color: #64748b;">
                        Showing <strong><span x-text="filteredRows.length"></span></strong> matching leads
                    </div>
                </div>

                <!-- 4 Major Categories Cards -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 25px;">
                    <div 
                        @click="categoryFilter = (categoryFilter === 'Data Analyst and Scientist' ? 'ALL' : 'Data Analyst and Scientist')"
                        :style="categoryFilter === 'Data Analyst and Scientist' ? 'border:2px solid var(--category-da)' : ''"
                        style="background: white; padding: 20px; border-radius: 14px; border: 1px solid var(--border-color); cursor: pointer;"
                    >
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <div style="padding: 8px; border-radius: 8px; background: var(--category-da-bg); color: var(--category-da);">
                                <i data-lucide="database" style="width:22px"></i>
                            </div>
                            <span class="badge-cat badge-da" x-text="getPercent('Data Analyst and Scientist') + '%'"></span>
                        </div>
                        <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Data Analyst & Scientist</h3>
                        <div style="font-size: 24px; font-weight: 800; color: var(--category-da);" x-text="getCatCount('Data Analyst and Scientist')"></div>
                    </div>

                    <div 
                        @click="categoryFilter = (categoryFilter === 'Accounting and Taxation' ? 'ALL' : 'Accounting and Taxation')"
                        :style="categoryFilter === 'Accounting and Taxation' ? 'border:2px solid var(--category-acc)' : ''"
                        style="background: white; padding: 20px; border-radius: 14px; border: 1px solid var(--border-color); cursor: pointer;"
                    >
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <div style="padding: 8px; border-radius: 8px; background: var(--category-acc-bg); color: var(--category-acc);">
                                <i data-lucide="briefcase" style="width:22px"></i>
                            </div>
                            <span class="badge-cat badge-acc" x-text="getPercent('Accounting and Taxation') + '%'"></span>
                        </div>
                        <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Accounting & Taxation</h3>
                        <div style="font-size: 24px; font-weight: 800; color: var(--category-acc);" x-text="getCatCount('Accounting and Taxation')"></div>
                    </div>

                    <div 
                        @click="categoryFilter = (categoryFilter === 'Full Stack Developer' ? 'ALL' : 'Full Stack Developer')"
                        :style="categoryFilter === 'Full Stack Developer' ? 'border:2px solid var(--category-dev)' : ''"
                        style="background: white; padding: 20px; border-radius: 14px; border: 1px solid var(--border-color); cursor: pointer;"
                    >
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <div style="padding: 8px; border-radius: 8px; background: var(--category-dev-bg); color: var(--category-dev);">
                                <i data-lucide="code" style="width:22px"></i>
                            </div>
                            <span class="badge-cat badge-dev" x-text="getPercent('Full Stack Developer') + '%'"></span>
                        </div>
                        <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Full Stack Developer</h3>
                        <div style="font-size: 24px; font-weight: 800; color: var(--category-dev);" x-text="getCatCount('Full Stack Developer')"></div>
                    </div>

                    <div 
                        @click="categoryFilter = (categoryFilter === 'Other' ? 'ALL' : 'Other')"
                        :style="categoryFilter === 'Other' ? 'border:2px solid var(--category-other)' : ''"
                        style="background: white; padding: 20px; border-radius: 14px; border: 1px solid var(--border-color); cursor: pointer;"
                    >
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <div style="padding: 8px; border-radius: 8px; background: var(--category-other-bg); color: var(--category-other);">
                                <i data-lucide="folder-plus" style="width:22px"></i>
                            </div>
                            <span class="badge-cat badge-other" x-text="getPercent('Other') + '%'"></span>
                        </div>
                        <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Other / General</h3>
                        <div style="font-size: 24px; font-weight: 800; color: var(--category-other);" x-text="getCatCount('Other')"></div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="card-panel">
                    <div class="table-wrapper">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Month</th>
                                    <th>Name</th>
                                    <th>Mob</th>
                                    <th>Email</th>
                                    <th>Raw Course</th>
                                    <th>Source</th>
                                    <th>Major Category</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, idx) in paginatedRows" :key="idx">
                                    <tr :class="{'duplicate-row': row.is_duplicate}">
                                        <td style="color:#64748b" x-text="((page - 1) * pageSize) + idx + 1"></td>
                                        <td style="font-size: 12px; font-weight:600" x-text="row.Date"></td>
                                        <td><strong style="color:var(--primary-navy)" x-text="row.Month"></strong></td>
                                        <td style="font-weight: 600;" x-text="row.Name"></td>
                                        <td><span style="font-family:monospace; font-weight:600" x-text="row.Mob"></span></td>
                                        <td><span style="font-weight:600" x-text="row.Email"></span></td>
                                        <td><code x-text="row.Raw_Course"></code></td>
                                        <td>
                                            <span style="background: #e0edff; color: var(--primary-navy); padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700;" x-text="row.Source"></span>
                                        </td>
                                        <td><span class="badge-cat badge-da" x-text="row.Major_Category"></span></td>
                                        <td>
                                            <template x-if="row.is_duplicate">
                                                <span style="background: #fee2e2; color: #dc2626; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">Duplicate</span>
                                            </template>
                                            <template x-if="!row.is_duplicate">
                                                <span style="background: #dcfce7; color: #16a34a; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">Unique</span>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: Saved Database Vault -->
        <div x-show="activeTab === 'vault'">
            <div class="card-panel">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <h2 style="font-size: 18px; font-weight: 700; color: var(--primary-navy); margin: 0; display: flex; align-items: center; gap: 8px;">
                            <i data-lucide="database" style="width:22px"></i> Saved MySQL Database Vault (<span x-text="filteredDbLeads.length"></span> Unique Records)
                        </h2>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-gold" @click="openExportModal('vault')">
                            <i data-lucide="sliders" style="width:16px"></i> Custom Dynamic Export Vault (.xlsx)
                        </button>
                    </div>
                </div>

                <!-- Source Filter inside Vault -->
                <div style="background: #f8fafc; border-radius: 10px; padding: 15px 20px; border: 1px solid var(--border-color); margin-bottom: 20px; display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 4px;">
                            🌐 Filter Vault by Source:
                        </label>
                        <select x-model="vaultSourceFilter" style="padding: 8px 14px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px; font-weight: 600;">
                            <option value="ALL">All Sources</option>
                            <template x-for="s in uniqueVaultSources" :key="s">
                                <option :value="s" x-text="s"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 4px;">
                            🎓 Filter Vault by Category:
                        </label>
                        <select x-model="vaultCategoryFilter" style="padding: 8px 14px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px; font-weight: 600;">
                            <option value="ALL">All Categories</option>
                            <option value="Data Analyst and Scientist">Data Analyst & Scientist</option>
                            <option value="Accounting and Taxation">Accounting & Taxation</option>
                            <option value="Full Stack Developer">Full Stack Developer</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Month</th>
                                <th>Name</th>
                                <th>Mob</th>
                                <th>Email</th>
                                <th>Raw Course</th>
                                <th>Source</th>
                                <th>Major Category</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="filteredDbLeads.length === 0">
                                <tr>
                                    <td colSpan="9" style="text-align:center; padding:30px; color:#64748b">
                                        No saved database leads match the selected filters.
                                    </td>
                                </tr>
                            </template>
                            <template x-for="(lead, idx) in filteredDbLeads" :key="idx">
                                <tr>
                                    <td style="color:#64748b" x-text="idx + 1"></td>
                                    <td style="font-size: 12px;" x-text="lead.Date || lead.date"></td>
                                    <td><strong style="color:var(--primary-navy)" x-text="lead.Month || lead.month"></strong></td>
                                    <td style="font-weight: 600;" x-text="lead.Name || lead.name"></td>
                                    <td style="font-family:monospace; font-weight:600" x-text="lead.Mob || lead.mob"></td>
                                    <td style="font-weight:600" x-text="lead.Email || lead.email"></td>
                                    <td><code x-text="lead.Raw_Course || lead.raw_course"></code></td>
                                    <td>
                                        <span style="background: #e0edff; color: var(--primary-navy); padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700;" x-text="lead.Source || lead.source || 'Direct/Organic'"></span>
                                    </td>
                                    <td><span class="badge-cat badge-da" x-text="lead.Major_Category || lead.major_category"></span></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- DYNAMIC CUSTOM EXPORT MODAL -->
        <div 
            x-show="exportModalOpen" 
            style="position: fixed; inset: 0; background: rgba(5,25,77,0.7); backdrop-filter: blur(6px); z-index: 999; display: flex; align-items: center; justify-content: center; padding: 20px;"
            x-transition
        >
            <div style="background: white; border-radius: 18px; width: 100%; max-width: 600px; padding: 30px; box-shadow: 0 25px 50px rgba(0,0,0,0.3); border: 1px solid var(--border-color);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 15px;">
                    <h3 style="font-size: 19px; font-weight: 800; color: var(--primary-navy); display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="sliders" style="width:22px; color:var(--accent-blue)"></i> 🎯 Dynamic Custom Excel Exporter
                    </h3>
                    <button @click="exportModalOpen = false" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b;">&times;</button>
                </div>

                <div style="background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; padding: 10px 14px; border-radius: 6px; font-size: 12px; margin-bottom: 20px;">
                    ⚠️ <strong>Export Rule Active</strong>: Rows with blank Phone number OR blank Email address are automatically excluded from exports!
                </div>

                <!-- Selectors -->
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--primary-navy); margin-bottom: 4px;">
                            1. Select Data Source:
                        </label>
                        <select x-model="exportTarget" style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 13px;">
                            <option value="current">Current Cleaned Workbook File (<span x-text="processedData.length"></span> rows)</option>
                            <option value="vault">MySQL Database Vault (<span x-text="dbLeads.length"></span> total rows)</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--primary-navy); margin-bottom: 4px;">
                            2. Filter by Category:
                        </label>
                        <select x-model="exportCategoryFilter" style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 13px;">
                            <option value="ALL">All Categories</option>
                            <option value="Data Analyst and Scientist">Data Analyst & Scientist</option>
                            <option value="Accounting and Taxation">Accounting & Taxation</option>
                            <option value="Full Stack Developer">Full Stack Developer</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div style="grid-column: span 2;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--primary-navy); margin-bottom: 4px;">
                            3. Filter by Lead Source:
                        </label>
                        <select x-model="exportSourceFilter" style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 13px;">
                            <option value="ALL">All Sources (Google, Facebook, Insta, etc.)</option>
                            <template x-for="s in uniqueSources" :key="s">
                                <option :value="s" x-text="s"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Column Checkboxes -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-size: 13px; font-weight: 700; color: var(--primary-navy); margin-bottom: 10px;">
                        4. Select Columns to Include in Excel Output:
                    </label>

                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid var(--border-color);">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" x-model="exportCols.name" style="width: 18px; height: 18px;">
                            👤 Student Name
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" x-model="exportCols.mob" style="width: 18px; height: 18px;">
                            📞 Mob / Phone Number
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" x-model="exportCols.email" style="width: 18px; height: 18px;">
                            ✉️ Email Address
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" x-model="exportCols.source" style="width: 18px; height: 18px;">
                            🌐 Lead Source
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" x-model="exportCols.date" style="width: 18px; height: 18px;">
                            📅 Date
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" x-model="exportCols.month" style="width: 18px; height: 18px;">
                            🗓️ Month
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" x-model="exportCols.course" style="width: 18px; height: 18px;">
                            🎓 Raw Course
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" x-model="exportCols.category" style="width: 18px; height: 18px;">
                            🏷️ Major Category
                        </label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button class="btn btn-outline" @click="exportModalOpen = false">Cancel</button>
                    <button class="btn btn-gold" @click="triggerDynamicExport()">
                        <i data-lucide="download" style="width:18px"></i> Download Filtered Excel (.xlsx) &rarr;
                    </button>
                </div>
            </div>
        </div>

</div>

<script>
function cleanerApp() {
    return {
        activeTab: 'cleaner',
        importType: 'file',
        googleUrl: '',
        fileName: '',
        headers: [],
        rawDataset: [],
        processedData: [],
        dbLeads: [],
        loading: false,
        successMsg: '',
        categoryFilter: 'ALL',
        sourceFilter: 'ALL',
        vaultCategoryFilter: 'ALL',
        vaultSourceFilter: 'ALL',
        searchTerm: '',
        hideDuplicates: false,
        page: 1,
        pageSize: 20,

        // DYNAMIC EXPORT MODAL STATE
        exportModalOpen: false,
        exportTarget: 'current',
        exportCategoryFilter: 'ALL',
        exportSourceFilter: 'ALL',
        exportCols: {
            name: true,
            mob: true,
            email: true,
            source: true,
            date: true,
            month: true,
            course: true,
            category: true
        },

        mappings: {
            nameCol: '',
            emailCol: '',
            phoneCol: '',
            dateCol: '',
            courseCol: '',
            sourceCol: ''
        },

        COURSE_MAP: {
            'AI': 'Data Analyst and Scientist',
            'DA+CGAI': 'Data Analyst and Scientist',
            'MSO+M-DA': 'Data Analyst and Scientist',
            'MIS+ASQ': 'Data Analyst and Scientist',
            'C-DA': 'Data Analyst and Scientist',
            'M-DA': 'Data Analyst and Scientist',
            'EX-DA': 'Data Analyst and Scientist',
            'EX': 'Data Analyst and Scientist',
            'ORACLE': 'Data Analyst and Scientist',
            'DA+DATA SCIENCE': 'Data Analyst and Scientist',
            'AD EX+PBI': 'Data Analyst and Scientist',
            'CBDA': 'Data Analyst and Scientist',
            'MIS+PBI': 'Data Analyst and Scientist',
            'PBI': 'Data Analyst and Scientist',
            'MIS': 'Data Analyst and Scientist',
            'ASQL': 'Data Analyst and Scientist',
            'BSQL': 'Data Analyst and Scientist',
            'M-DA+VBA': 'Data Analyst and Scientist',
            'AD EX+PBI+ASQL': 'Data Analyst and Scientist',
            'MSO+MIS': 'Data Analyst and Scientist',
            'ADVANCE EXCEL+VBA': 'Data Analyst and Scientist',
            'MSO+AD EXCEL': 'Data Analyst and Scientist',
            'ACBDA': 'Data Analyst and Scientist',
            'DATA ANALYTICS': 'Data Analyst and Scientist',
            'EX+SQL': 'Data Analyst and Scientist',
            'MCDA': 'Data Analyst and Scientist',
            'AD EX+PBI+BSQL': 'Data Analyst and Scientist',
            'EX+PBI+ASQL': 'Data Analyst and Scientist',
            'MCBDA': 'Data Analyst and Scientist',
            'MDA': 'Data Analyst and Scientist',
            'MDA+CDS': 'Data Analyst and Scientist',
            'MDA+PCDS': 'Data Analyst and Scientist',
            'MDA+CGAI': 'Data Analyst and Scientist',
            'EX+VBA': 'Data Analyst and Scientist',
            'CDS': 'Data Analyst and Scientist',
            'PCDS': 'Data Analyst and Scientist',
            'CGA': 'Data Analyst and Scientist',
            'CGAI': 'Data Analyst and Scientist',
            'MIS+TALLY': 'Data Analyst and Scientist',
            'PYCA': 'Data Analyst and Scientist',
            'DATA SCIENCE': 'Data Analyst and Scientist',
            'PBI+MI': 'Data Analyst and Scientist',
            'PBI+ASQL': 'Data Analyst and Scientist'
        },

        initData() {
            this.loadDatabaseVault();
            setTimeout(() => { if (window.lucide) lucide.createIcons(); }, 100);
        },

        get uniqueSources() {
            const sources = new Set();
            this.processedData.forEach(r => {
                if (r.Source) sources.add(r.Source);
            });
            return Array.from(sources);
        },

        get uniqueVaultSources() {
            const sources = new Set();
            this.dbLeads.forEach(r => {
                const s = r.Source || r.source || 'Direct/Organic';
                sources.add(s);
            });
            return Array.from(sources);
        },

        openExportModal(target) {
            this.exportTarget = target;
            this.exportModalOpen = true;
            setTimeout(() => { if (window.lucide) lucide.createIcons(); }, 50);
        },

        triggerDynamicExport() {
            let source = (this.exportTarget === 'vault') ? this.dbLeads : this.processedData;
            
            if (this.exportTarget === 'current' && this.hideDuplicates) {
                source = source.filter(r => !r.is_duplicate);
            }

            if (this.exportCategoryFilter !== 'ALL') {
                source = source.filter(r => (r.Major_Category || r.major_category) === this.exportCategoryFilter);
            }

            if (this.exportSourceFilter !== 'ALL') {
                source = source.filter(r => (r.Source || r.source) === this.exportSourceFilter);
            }

            // STRICT RULE: EXCLUDE / AVOID ROWS WHERE PHONE OR EMAIL IS BLANK
            const validLeads = source.filter(r => {
                const phone = String(r.Mob || r.mob || '').trim();
                const email = String(r.Email || r.email || '').trim();
                return phone !== '' && email !== '';
            });

            if (validLeads.length === 0) {
                return alert('No matching records found that have BOTH a valid Phone Number and valid Email!');
            }

            const dynamicRows = validLeads.map(r => {
                const rowObj = {};
                if (this.exportCols.name) rowObj['Name'] = r.Name || r.name || '';
                if (this.exportCols.mob) rowObj['Mob'] = r.Mob || r.mob || '';
                if (this.exportCols.email) rowObj['Email'] = r.Email || r.email || '';
                if (this.exportCols.source) rowObj['Source'] = r.Source || r.source || 'Direct/Organic';
                if (this.exportCols.date) rowObj['Date'] = r.Date || r.date || '';
                if (this.exportCols.month) rowObj['Month'] = r.Month || r.month || '';
                if (this.exportCols.course) rowObj['Raw Course'] = r.Raw_Course || r.raw_course || '';
                if (this.exportCols.category) rowObj['Major Category'] = r.Major_Category || r.major_category || '';
                return rowObj;
            });

            const ws = XLSX.utils.json_to_sheet(dynamicRows);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Filtered_Valid_Leads");

            const filename = `Clean_Leads_Export_${new Date().toISOString().slice(0,10)}.xlsx`;
            XLSX.writeFile(wb, filename, { bookType: 'xlsx' });

            this.exportModalOpen = false;
        },

        loadDatabaseVault() {
            fetch('/api/database-leads')
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        this.dbLeads = res.leads;
                    }
                })
                .catch(() => {
                    const stored = localStorage.getItem('f1mtech_db_leads');
                    if (stored) {
                        try { this.dbLeads = JSON.parse(stored); } catch(e) { this.dbLeads = []; }
                    }
                });
        },

        saveToDatabaseVault() {
            const uniqueLeads = this.processedData.filter(r => !r.is_duplicate);
            if (uniqueLeads.length === 0) return alert('No unique leads to save!');

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
                    alert(res.message);
                    this.loadDatabaseVault();
                } else {
                    alert('Error saving to database: ' + (res.error || 'Unknown error'));
                }
            })
            .catch(() => {
                const combined = [...uniqueLeads, ...this.dbLeads];
                this.dbLeads = combined;
                localStorage.setItem('f1mtech_db_leads', JSON.stringify(combined));
                alert(`Saved unique leads to Vault database!`);
            });
        },

        loadSampleDataset() {
            const sample = [
                {"Date": "2024-05-15", "Student Name": "Rahul Sharma", "Mobile No": "9818845002", "Email Address": "rahul.sharma@gmaill.com", "Course Name": "AI", "Lead Source": "Google Ads"},
                {"Date": "", "Student Name": "Priya Verma", "Mobile No": "9990349899", "Email Address": "priya.v@gmail.com", "Course Name": "DA+CGAI", "Lead Source": "Facebook"},
                {"Date": "", "Student Name": "Amit Kumar", "Mobile No": "", "Email Address": "amit.k@gmai.com", "Course Name": "MSO+M-DA", "Lead Source": "Instagram"},
                {"Date": "2024-07-12", "Student Name": "Duplicate Entry", "Mobile No": "9818845002", "Email Address": "rahul.dup@gmail.com", "Course Name": "Python", "Lead Source": "Google Ads"},
                {"Date": "2024-07-12", "Student Name": "Sneha Gupta", "Mobile No": "8800112233", "Email Address": "", "Course Name": "Digital Marketing", "Lead Source": "Website Direct"},
                {"Date": "2024-07-25", "Student Name": "Neha Rani", "Mobile No": "9711223344", "Email Address": "neha.rani@gmail.com", "Course Name": "Tally GST", "Lead Source": "JustDial"}
            ];

            this.fileName = "Sample_Leads_Dataset.xlsx";
            this.rawDataset = sample;
            this.headers = Object.keys(sample[0]);
            this.autoMapHeaders();
            this.successMsg = "Loaded Sample Dataset with Lead Source & Deduplication!";
        },

        handleFileUpload(e) {
            const file = e.target.files[0];
            if (!file) return;

            this.fileName = file.name;
            const reader = new FileReader();

            if (file.name.endsWith('.csv')) {
                Papa.parse(file, {
                    header: true,
                    skipEmptyLines: true,
                    complete: (res) => {
                        this.rawDataset = res.data;
                        this.headers = Object.keys(res.data[0] || {});
                        this.autoMapHeaders();
                        this.successMsg = `Loaded ${res.data.length} rows from CSV`;
                    }
                });
            } else {
                reader.onload = (evt) => {
                    const wb = XLSX.read(evt.target.result, { type: 'binary', cellDates: true, dateNF: 'yyyy-mm-dd' });
                    const compiled = [];

                    wb.SheetNames.forEach(sheetName => {
                        const ws = wb.Sheets[sheetName];
                        const sheetRows = XLSX.utils.sheet_to_json(ws, { defval: '', raw: false });
                        sheetRows.forEach(row => {
                            row['_sheet_name'] = sheetName;
                            compiled.push(row);
                        });
                    });

                    this.rawDataset = compiled;
                    this.headers = Object.keys(compiled[0] || {});
                    this.autoMapHeaders();
                    this.successMsg = `Compiled ${compiled.length} rows from all sheets inside Excel file!`;
                };
                reader.readAsBinaryString(file);
            }
        },

        fetchGoogleSheet() {
            if (!this.googleUrl) return;
            const match = this.googleUrl.match(/\/d\/([a-zA-Z0-9-_]+)/);
            if (!match) return alert('Invalid Google Sheet Link');

            this.loading = true;
            fetch(`https://docs.google.com/spreadsheets/d/${match[1]}/export?format=csv`)
                .then(r => r.text())
                .then(csv => {
                    Papa.parse(csv, {
                        header: true,
                        skipEmptyLines: true,
                        complete: (res) => {
                            this.loading = false;
                            this.rawDataset = res.data;
                            this.headers = Object.keys(res.data[0] || {});
                            this.autoMapHeaders();
                            this.successMsg = `Fetched ${res.data.length} rows from Google Sheet`;
                        }
                    });
                })
                .catch(() => { this.loading = false; alert('Could not fetch sheet. Ensure link is public.'); });
        },

        autoMapHeaders() {
            this.mappings = { nameCol: '', emailCol: '', phoneCol: '', dateCol: '', courseCol: '', sourceCol: '' };

            this.headers.forEach(h => {
                const cleanH = h.toLowerCase().trim().replace(/[^a-z0-9]/g, '');

                if (!this.mappings.phoneCol && (cleanH.includes('phone') || cleanH.includes('mob') || cleanH.includes('mobile') || cleanH.includes('contact') || cleanH.includes('whatsapp') || cleanH.includes('ph') || cleanH.includes('num') || cleanH.includes('cell'))) {
                    this.mappings.phoneCol = h;
                }
                if (!this.mappings.emailCol && (cleanH.includes('email') || cleanH.includes('mail') || cleanH.includes('gmail') || cleanH.includes('yahoo'))) {
                    this.mappings.emailCol = h;
                }
                if (!this.mappings.nameCol && (cleanH.includes('name') || cleanH.includes('student') || cleanH.includes('candidate') || cleanH.includes('person') || cleanH.includes('user'))) {
                    this.mappings.nameCol = h;
                }
                if (!this.mappings.dateCol && (cleanH.includes('date') || cleanH.includes('dob') || cleanH.includes('reg') || cleanH.includes('created') || cleanH.includes('time'))) {
                    this.mappings.dateCol = h;
                }
                if (!this.mappings.courseCol && (cleanH.includes('course') || cleanH.includes('subject') || cleanH.includes('stream') || cleanH.includes('tech') || cleanH.includes('program') || cleanH.includes('interest'))) {
                    this.mappings.courseCol = h;
                }
                if (!this.mappings.sourceCol && (cleanH.includes('source') || cleanH.includes('platform') || cleanH.includes('channel') || cleanH.includes('campaign') || cleanH.includes('utm') || cleanH.includes('medium') || cleanH.includes('origin') || cleanH.includes('vendor'))) {
                    this.mappings.sourceCol = h;
                }
            });

            this.runCleaningEngine();
        },

        parseRealDateAndMonth(val) {
            if (val === null || val === undefined || val === '') return { date: '', month: '' };
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

            if (typeof val === 'number' || (!isNaN(val) && !String(val).includes('-') && !String(val).includes('/') && !String(val).includes('.'))) {
                const num = parseFloat(val);
                if (num > 10000 && num < 100000) {
                    const utc_days  = Math.floor(num - 25569);
                    const utc_value = utc_days * 86400;
                    const date_info = new Date(utc_value * 1000);
                    
                    const year = date_info.getUTCFullYear();
                    const month = String(date_info.getUTCMonth() + 1).padStart(2, '0');
                    const day = String(date_info.getUTCDate()).padStart(2, '0');
                    
                    return { date: `${year}-${month}-${day}`, month: monthNames[date_info.getUTCMonth()] };
                }
            }

            let str = String(val).trim();
            if (!str) return { date: '', month: '' };

            let parts = str.match(/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})$/);
            if (parts) {
                let day = parseInt(parts[1], 10);
                let month = parseInt(parts[2], 10) - 1;
                let year = parseInt(parts[3], 10);
                if (year < 100) year += 2000;

                if (month >= 0 && month < 12 && day >= 1 && day <= 31) {
                    const formattedDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    return { date: formattedDate, month: monthNames[month] };
                }
            }

            return { date: str, month: '' };
        },

        shouldExcludeRow(row) {
            const fullRowText = Object.values(row).join(' ').toLowerCase();
            if (fullRowText.includes('enrolled')) return true;
            if (fullRowText.includes('no need to call')) return true;
            if (fullRowText.includes('student data')) return true;
            return false;
        },

        categorizeCourse(courseStr) {
            if (!courseStr) return 'Other';
            const upper = String(courseStr).trim().toUpperCase();
            if (this.COURSE_MAP[upper]) return this.COURSE_MAP[upper];

            if (upper.includes('MDA') || upper.includes('M-DA') || upper.includes('DATA ANALYTIC') || upper.includes('DATA SCIENCE') || upper.includes('POWER BI') || upper.includes('EXCEL') || upper.includes('SQL') || upper.includes('CGAI') || upper === 'AI' || upper.includes('AI ') || upper.includes(' AI')) {
                return 'Data Analyst and Scientist';
            }
            if (upper.includes('TALLY') || upper.includes('GST') || upper.includes('TAX') || upper.includes('ACCOUNT') || upper.includes('ITR') || upper.includes('BUSY') || upper.includes('SAP')) {
                return 'Accounting and Taxation';
            }
            if (upper.includes('DEVELOP') || upper.includes('FULL STACK') || upper.includes('MERN') || upper.includes('MEAN') || upper.includes('JAVA') || upper.includes('PHP') || upper.includes('.NET') || upper.includes('DSA')) {
                return 'Full Stack Developer';
            }
            return 'Other';
        },

        cleanName(nameStr) {
            if (!nameStr) return '';
            let cleaned = String(nameStr).replace(/[^a-zA-Z\s]/g, '').trim().replace(/\s+/g, ' ');
            if (!cleaned) return '';
            return cleaned.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
        },

        cleanEmail(emailStr) {
            if (!emailStr) return '';
            let str = String(emailStr).trim().toLowerCase();
            if (!str) return '';

            const match = str.match(/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/);
            if (match) {
                let e = match[0].replace(/@gmaill?\.com$/i, '@gmail.com').replace(/@gmai\.com$/i, '@gmail.com');
                return e;
            }
            return '';
        },

        cleanPhone(phoneStr) {
            if (!phoneStr) return '';
            let str = String(phoneStr).trim();
            if (!str) return '';

            let digits = str.replace(/\D/g, '');
            if (digits.length === 12 && digits.startsWith('91')) digits = digits.slice(2);
            if (digits.length === 11 && digits.startsWith('0')) digits = digits.slice(1);

            if (digits.length >= 7) {
                return digits;
            }
            return '';
        },

        isRowEmpty(row) {
            return Object.values(row).every(v => !v || !String(v).trim());
        },

        runCleaningEngine() {
            const result = [];
            const seenPhones = new Set();
            let counter = 1;

            let lastValidDate = '';
            let lastValidMonth = '';

            // DB phone set for strict multi-upload deduplication
            const dbPhones = new Set();
            this.dbLeads.forEach(r => {
                const p = r.Mob || r.mob;
                if (p) dbPhones.add(p);
            });

            this.rawDataset.forEach(row => {
                if (this.isRowEmpty(row)) return;
                if (this.shouldExcludeRow(row)) return;

                const rawDate   = this.mappings.dateCol ? row[this.mappings.dateCol] : '';
                const rawName   = this.mappings.nameCol ? row[this.mappings.nameCol] : '';
                const rawPhone  = this.mappings.phoneCol ? row[this.mappings.phoneCol] : '';
                const rawEmail  = this.mappings.emailCol ? row[this.mappings.emailCol] : '';
                const rawCourse = this.mappings.courseCol ? row[this.mappings.courseCol] : '';
                const rawSource = this.mappings.sourceCol ? row[this.mappings.sourceCol] : '';

                const nameVal   = this.cleanName(rawName);
                const emailVal  = this.cleanEmail(rawEmail);
                const phoneVal  = this.cleanPhone(rawPhone);
                const sourceVal = String(rawSource || '').trim() || 'Direct/Organic';
                const parsedDateObj = this.parseRealDateAndMonth(rawDate);

                let dateVal = parsedDateObj.date;
                let monthVal = parsedDateObj.month;

                if (dateVal && dateVal !== '') {
                    lastValidDate = dateVal;
                    lastValidMonth = monthVal || lastValidMonth;
                } else {
                    dateVal = lastValidDate;
                    monthVal = lastValidMonth;
                }

                const courseVal = String(rawCourse || '').trim();
                const categoryVal = this.categorizeCourse(courseVal);

                let isDup = false;
                if (phoneVal !== '') {
                    if (seenPhones.has(phoneVal) || dbPhones.has(phoneVal)) {
                        isDup = true;
                    } else {
                        seenPhones.add(phoneVal);
                    }
                }

                result.push({
                    _id: counter++,
                    sheet_name: row['_sheet_name'] || 'Sheet1',
                    Date: dateVal,
                    Month: monthVal,
                    Name: nameVal,
                    Mob: phoneVal,
                    Email: emailVal,
                    Raw_Course: courseVal || '',
                    Major_Category: categoryVal,
                    Source: sourceVal,
                    is_duplicate: isDup
                });
            });

            this.processedData = result;
            setTimeout(() => { if (window.lucide) lucide.createIcons(); }, 100);
        },

        get filteredRows() {
            return this.processedData.filter(r => {
                if (this.hideDuplicates && r.is_duplicate) return false;
                if (this.categoryFilter !== 'ALL' && r.Major_Category !== this.categoryFilter) return false;
                if (this.sourceFilter !== 'ALL' && r.Source !== this.sourceFilter) return false;
                return true;
            });
        },

        get filteredDbLeads() {
            return this.dbLeads.filter(r => {
                const cat = r.Major_Category || r.major_category;
                const src = r.Source || r.source || 'Direct/Organic';

                if (this.vaultCategoryFilter !== 'ALL' && cat !== this.vaultCategoryFilter) return false;
                if (this.vaultSourceFilter !== 'ALL' && src !== this.vaultSourceFilter) return false;
                return true;
            });
        },

        get paginatedRows() {
            const start = (this.page - 1) * this.pageSize;
            return this.filteredRows.slice(start, start + this.pageSize);
        }
    };
}
</script>
@endsection
