<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - F1 MTech Data Cleaner & Vault</title>
    <link rel="icon" type="image/webp" href="https://f1mtech.com/public/uploads/202512031446logo_f1.webp">
    
    <!-- Google Fonts Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --primary-navy: #082a7d;
            --accent-gold: #ffc107;
            --gradient-primary: linear-gradient(135deg, #05194d 0%, #082a7d 50%, #2467ec 100%);
            --gradient-gold: linear-gradient(135deg, #ffb703 0%, #fb8500 100%);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Poppins', sans-serif; 
            background: var(--gradient-primary); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 20px;
            color: #1e293b;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(16px);
            width: 100%;
            max-width: 440px;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-badge {
            background: var(--primary-navy);
            color: white;
            padding: 10px 22px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 22px;
            display: inline-block;
            margin-bottom: 12px;
            box-shadow: 0 4px 14px rgba(8,42,125,0.3);
        }

        .login-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--primary-navy);
            margin-bottom: 4px;
        }

        .login-subtitle {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--primary-navy);
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            width: 18px;
            height: 18px;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border-radius: 10px;
            border: 1.5px solid #cbd5e1;
            font-size: 14px;
            outline: none;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .form-input:focus {
            border-color: #2467ec;
            box-shadow: 0 0 0 4px rgba(36,103,236,0.15);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(8,42,125,0.3);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(8,42,125,0.4);
        }

        .error-alert {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .demo-credentials {
            margin-top: 25px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px dashed #cbd5e1;
            font-size: 12px;
            color: #475569;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <div class="logo-badge">F1M</div>
            <h1 class="login-title">F1 MTECH DATA VAULT</h1>
            <p class="login-subtitle">Authentication Required to Access Lead Cleaner & Database</p>
        </div>

        @if($errors->has('login_error'))
            <div class="error-alert">
                <i data-lucide="alert-circle" style="width:18px"></i>
                <span>{{ $errors->first('login_error') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email">User ID / Email Address</label>
                <div class="input-wrapper">
                    <i data-lucide="user" class="input-icon"></i>
                    <input 
                        type="text" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="Enter admin@f1mtech.com or User ID" 
                        value="{{ old('email', 'admin@f1mtech.com') }}" 
                        required 
                        autofocus
                    >
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrapper">
                    <i data-lucide="lock" class="input-icon"></i>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="Enter password" 
                        value="admin123"
                        required
                    >
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; font-size: 13px;">
                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; color: #64748b;">
                    <input type="checkbox" name="remember" checked style="width: 16px; height: 16px;">
                    Remember login
                </label>
            </div>

            <button type="submit" class="btn-submit">
                <i data-lucide="log-in" style="width:18px"></i> Secure Login &rarr;
            </button>
        </form>

        <div class="demo-credentials">
            <strong style="color: var(--primary-navy); display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                <i data-lucide="shield" style="width:14px"></i> Default Admin Credentials:
            </strong>
            <div>• ID / Email: <strong>admin@f1mtech.com</strong></div>
            <div>• Password: <strong>admin123</strong></div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
