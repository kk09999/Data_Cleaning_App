<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 MTech - AI Data Cleaning & Database Vault</title>
    <link rel="icon" type="image/webp" href="https://f1mtech.com/public/uploads/202512031446logo_f1.webp">
    
    <!-- Google Fonts Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons & Alpine.js CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- SheetJS & PapaParse -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/papaparse@5.4.1/papaparse.min.js"></script>

    <style>
        :root {
            --primary-navy: #082a7d;
            --secondary-navy: #26305f;
            --accent-blue: #2467ec;
            --gradient-primary: linear-gradient(135deg, #082a7d 0%, #2467ec 100%);
            --gradient-gold: linear-gradient(135deg, #ffb703 0%, #fb8500 100%);
            --accent-gold: #ffc107;
            --bg-light: #f4f6fb;
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
        body { font-family: 'Poppins', sans-serif; background: var(--bg-light); color: #1e293b; line-height: 1.6; }

        /* F1 MTech Topbar */
        .topbar { background: #05194d; color: #d1e3ff; padding: 8px 0; font-size: 13px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .topbar-container { max-width: 1320px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
        .topbar-link { color: #e2e8f0; text-decoration: none; display: flex; align-items: center; gap: 6px; }
        .topbar-link:hover { color: var(--accent-gold); }

        /* Main Header */
        .main-header { background: var(--primary-navy); color: white; padding: 14px 0; box-shadow: 0 4px 20px rgba(0,0,0,0.2); position: sticky; top: 0; z-index: 100; }
        .header-container { max-width: 1320px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
        .logo-badge { background: white; padding: 6px 14px; border-radius: 8px; font-weight: 800; color: var(--primary-navy); font-size: 20px; }
        .logo-title { font-weight: 700; font-size: 18px; line-height: 1.2; }
        .logo-subtitle { font-size: 11px; color: var(--accent-gold); text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }

        /* Layout Container */
        .app-container { max-width: 1320px; margin: 30px auto; padding: 0 20px; }

        /* Cards */
        .card-panel { background: white; border-radius: 14px; padding: 25px; box-shadow: 0 2px 10px rgba(8,42,125,0.06); border: 1px solid var(--border-color); margin-bottom: 25px; }
        .card-title { font-size: 18px; font-weight: 700; color: var(--primary-navy); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px; font-size: 14px; font-weight: 600; border-radius: 50px; border: none; cursor: pointer; transition: all 0.2s ease; text-decoration: none; }
        .btn-primary { background: var(--gradient-primary); color: white; box-shadow: 0 4px 14px rgba(8,42,125,0.25); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(8,42,125,0.35); }
        .btn-gold { background: var(--gradient-gold); color: #1e293b; font-weight: 700; box-shadow: 0 4px 14px rgba(255,183,3,0.35); }
        .btn-gold:hover { transform: translateY(-2px); }
        .btn-outline { background: transparent; border: 1.5px solid var(--border-color); color: #1e293b; }
        .btn-outline:hover { background: #f8fafc; border-color: var(--primary-navy); color: var(--primary-navy); }

        /* Category Badges */
        .badge-cat { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-da { background: var(--category-da-bg); color: var(--category-da); }
        .badge-acc { background: var(--category-acc-bg); color: var(--category-acc); }
        .badge-dev { background: var(--category-dev-bg); color: var(--category-dev); }
        .badge-other { background: var(--category-other-bg); color: var(--category-other); }

        /* Tables */
        .table-wrapper { overflow-x: auto; border-radius: 8px; border: 1px solid var(--border-color); }
        .custom-table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
        .custom-table th { background: #082a7d; color: #ffffff; font-weight: 700; padding: 12px 16px; border-bottom: 2px solid #05194d; white-space: nowrap; }
        .custom-table td { padding: 12px 16px; border-bottom: 1px solid var(--border-color); }
        .custom-table tbody tr:hover { background: #f8fafc; }
        .duplicate-row { background: #fff1f2; }
    </style>
</head>
<body>

    <!-- F1 MTech Topbar -->
    <div class="topbar">
        <div class="topbar-container">
            <div style="display: flex; gap: 20px;">
                <span class="topbar-link"><i data-lucide="globe" style="width:14px"></i> F1 MACRO Technologies & Media</span>
                <a href="tel:+919818845002" class="topbar-link"><i data-lucide="phone" style="width:14px"></i> +91 9818845002</a>
                <a href="mailto:info@f1mtech.com" class="topbar-link"><i data-lucide="mail" style="width:14px"></i> info@f1mtech.com</a>
            </div>
            <div>
                <a href="https://f1mtech.com" target="_blank" class="topbar-link" style="color: var(--accent-gold); font-weight: 600;">Visit f1mtech.com &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="main-header">
        <div class="header-container">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="logo-badge">F1M</div>
                <div style="display: flex; flex-direction: column;">
                    <span class="logo-title">F1 MTECH DATA CLEANER & VAULT</span>
                    <span class="logo-subtitle">Authenticated Administrator Portal</span>
                </div>
            </div>

            @auth
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="background: rgba(255,255,255,0.15); padding: 6px 16px; border-radius: 30px; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="shield-check" style="width:16px; color:#ffc107"></i> Logged in as: <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})
                </div>

                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-gold" style="padding: 6px 16px; font-size: 12px;">
                        <i data-lucide="log-out" style="width:14px"></i> Logout
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </header>

    <!-- App Content -->
    <div class="app-container">
        @yield('content')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });
    </script>
</body>
</html>
