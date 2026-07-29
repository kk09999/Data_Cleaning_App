<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Macrotechnologies - Admin Portal & BI Data Vault</title>
    <link rel="icon" type="image/webp" href="https://f1mtech.com/public/uploads/202512031446logo_f1.webp">
    
    <!-- Google Fonts Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons, SheetJS, PapaParse & Chart.js CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/papaparse@5.4.1/papaparse.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary-navy: #05194d;
            --secondary-navy: #082a7d;
            --accent-blue: #2467ec;
            --gradient-primary: linear-gradient(135deg, #05194d 0%, #082a7d 100%);
            --gradient-gold: linear-gradient(135deg, #ffb703 0%, #fb8500 100%);
            --accent-gold: #ffc107;
            --bg-light: #f1f5f9;
            --sidebar-width: 270px;
            --border-color: #e2e8f0;

            --category-da: #0284c7;
            --category-da-bg: #e0f2fe;

            --category-acc: #059669;
            --category-acc-bg: #d1fae5;

            --category-dev: #7c3aed;
            --category-dev-bg: #f3e8ff;

            --category-other: #d97706;
            --category-other-bg: #fef3c7;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg-light); color: #1e293b; line-height: 1.5; overflow-x: hidden; }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* LEFT SIDEBAR MENU */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-navy);
            color: #e2e8f0;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 4px 0 25px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
        }

        .sidebar-brand {
            padding: 22px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            background: rgba(0,0,0,0.15);
        }

        .brand-badge {
            background: white;
            color: var(--primary-navy);
            font-weight: 800;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 18px;
        }

        .brand-text { display: flex; flex-direction: column; }
        .brand-title { font-weight: 800; font-size: 15px; color: white; letter-spacing: 0.5px; }
        .brand-subtitle { font-size: 10px; color: var(--accent-gold); text-transform: uppercase; font-weight: 700; letter-spacing: 1px; }

        .sidebar-menu {
            list-style: none;
            padding: 20px 14px;
            flex: 1;
            overflow-y: auto;
        }

        .menu-label {
            font-size: 10.5px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 10px 14px 6px 14px;
        }

        .menu-item {
            margin-bottom: 6px;
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .menu-link:hover {
            background: rgba(255,255,255,0.08);
            color: white;
            transform: translateX(3px);
        }

        .menu-link.active {
            background: var(--accent-blue);
            color: white;
            box-shadow: 0 4px 15px rgba(36,103,236,0.4);
        }

        .sidebar-user {
            padding: 18px 20px;
            border-top: 1px solid rgba(255,255,255,0.08);
            background: rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* MAIN CONTENT AREA */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .top-navbar {
            background: white;
            border-bottom: 1px solid var(--border-color);
            padding: 14px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 900;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }

        .page-content {
            padding: 30px;
            flex: 1;
        }

        /* UI Utilities */
        .card-panel { background: white; border-radius: 14px; padding: 25px; box-shadow: 0 2px 10px rgba(8,42,125,0.06); border: 1px solid var(--border-color); margin-bottom: 25px; }
        .card-title { font-size: 18px; font-weight: 700; color: var(--primary-navy); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 9px 20px; font-size: 13.5px; font-weight: 600; border-radius: 50px; border: none; cursor: pointer; transition: all 0.2s ease; text-decoration: none; }
        .btn-primary { background: var(--secondary-navy); color: white; box-shadow: 0 4px 14px rgba(8,42,125,0.2); }
        .btn-primary:hover { background: var(--primary-navy); transform: translateY(-1px); }
        .btn-gold { background: var(--gradient-gold); color: #1e293b; font-weight: 700; box-shadow: 0 4px 14px rgba(255,183,3,0.3); }
        .btn-gold:hover { transform: translateY(-1px); }
        .btn-outline { background: transparent; border: 1.5px solid var(--border-color); color: #1e293b; }
        .btn-outline:hover { background: #f8fafc; border-color: var(--primary-navy); color: var(--primary-navy); }

        .badge-cat { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-da { background: var(--category-da-bg); color: var(--category-da); }
        .badge-acc { background: var(--category-acc-bg); color: var(--category-acc); }
        .badge-dev { background: var(--category-dev-bg); color: var(--category-dev); }
        .badge-other { background: var(--category-other-bg); color: var(--category-other); }

        .table-wrapper { overflow-x: auto; border-radius: 10px; border: 1px solid var(--border-color); }
        .custom-table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
        .custom-table th { background: #05194d; color: #ffffff; font-weight: 700; padding: 12px 16px; border-bottom: 2px solid #082a7d; white-space: nowrap; }
        .custom-table td { padding: 12px 16px; border-bottom: 1px solid var(--border-color); }
        .custom-table tbody tr:hover { background: #f8fafc; }
        .duplicate-row { background: #fff1f2; }
    </style>
</head>
<body>

    <div class="admin-layout">
        
        <!-- MAIN CONTENT AREA -->
        <div class="main-content" style="margin-left: 0;">
            <!-- Top Navigation Bar -->
            <div class="top-navbar">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="background: var(--primary-navy); color: white; padding: 6px 14px; border-radius: 8px; font-weight: 800; font-size: 18px;">F1M</div>
                    <div>
                        <h1 style="font-size: 17px; font-weight: 800; color: var(--primary-navy); margin: 0;">F1 MACROTECHNOLOGIES ENTERPRISE ADMIN PORTAL</h1>
                        <span style="font-size: 11px; color: #64748b; font-weight: 600;">Lead Quality Engine, MySQL Vault & BI Analytics</span>
                    </div>
                </div>

                @auth
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="background: #f1f5f9; padding: 6px 16px; border-radius: 30px; font-size: 12.5px; display: flex; align-items: center; gap: 8px; border: 1px solid var(--border-color);">
                        <i data-lucide="shield-check" style="width:16px; color:#f59e0b"></i>
                        <span>Admin: <strong>{{ auth()->user()->name }}</strong></span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-gold" style="padding: 7px 16px; font-size: 12px;">
                            <i data-lucide="log-out" style="width:14px"></i> Logout
                        </button>
                    </form>
                </div>
                @endauth
            </div>

            <!-- Page Content -->
            <div class="page-content">
                @yield('content')
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });
    </script>
</body>
</html>
