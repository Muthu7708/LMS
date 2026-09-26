<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Finance ERP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --accent: #06b6d4;
            --bg-base: #0f1117;
            --bg-card: #1e2433;
            --border: rgba(255,255,255,0.07);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-base);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin: 0;
        }

        /* Animated background grid */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(99,102,241,0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99,102,241,0.06) 1px, transparent 1px);
            background-size: 48px 48px;
            animation: gridShift 20s linear infinite;
        }

        @keyframes gridShift {
            from { background-position: 0 0; }
            to   { background-position: 48px 48px; }
        }

        /* Glow orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.3;
            animation: float 8s ease-in-out infinite;
        }

        .orb-1 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, #6366f1, transparent);
            top: -100px; left: -100px;
        }

        .orb-2 {
            width: 350px; height: 350px;
            background: radial-gradient(circle, #06b6d4, transparent);
            bottom: -100px; right: -100px;
            animation-delay: -4s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%       { transform: translate(30px, -30px) scale(1.05); }
            66%       { transform: translate(-20px, 20px) scale(0.95); }
        }

        /* Card */
        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 48px 40px;
            background: rgba(30, 36, 51, 0.85);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            backdrop-filter: blur(20px);
            box-shadow: 0 25px 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(99,102,241,0.1);
            animation: cardIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: scale(0.9) translateY(20px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* Logo */
        .login-logo {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #6366f1, #06b6d4);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            margin-bottom: 16px;
            box-shadow: 0 8px 24px rgba(99,102,241,0.5);
        }

        .logo-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-primary);
            margin: 0;
        }

        .logo-sub {
            font-size: 13px;
            color: var(--text-muted);
            margin: 4px 0 0;
        }

        /* Form */
        .form-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
        }

        .form-control {
            background: rgba(15,17,23,0.6) !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            color: var(--text-primary) !important;
            border-radius: 10px !important;
            padding: 11px 40px 11px 16px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control::placeholder { color: var(--text-muted) !important; }

        .form-control:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.2) !important;
            background: rgba(15,17,23,0.8) !important;
        }

        .input-wrapper { position: relative; }

        .input-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px;
            cursor: pointer;
            transition: color 0.2s;
        }

        .input-icon:hover { color: var(--text-secondary); }

        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            color: white;
            border-radius: 10px;
            padding: 13px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.02em;
            cursor: pointer;
            transition: all 0.25s;
            box-shadow: 0 6px 20px rgba(99,102,241,0.4);
            margin-top: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(99,102,241,0.55);
        }

        .btn-login:active { transform: translateY(0); }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 16px;
        }

        .form-check-label {
            color: var(--text-secondary);
            font-size: 13px;
            cursor: pointer;
        }

        .form-check-input {
            background-color: rgba(15,17,23,0.6) !important;
            border-color: rgba(255,255,255,0.2) !important;
        }

        .form-check-input:checked {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
        }

        /* Error */
        .is-invalid { border-color: #ef4444 !important; }
        .invalid-feedback { color: #f87171; font-size: 12px; }

        .alert-danger {
            background: rgba(239,68,68,0.12);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 10px;
            color: #f87171;
            font-size: 13px;
            padding: 10px 14px;
            margin-bottom: 20px;
        }

        .login-footer {
            text-align: center;
            margin-top: 28px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .login-footer a { color: var(--text-muted); text-decoration: none; }

        /* Demo credentials box */
        .demo-box {
            background: rgba(99,102,241,0.08);
            border: 1px solid rgba(99,102,241,0.15);
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 12px;
            color: #818cf8;
        }

        .demo-box strong { color: #a5b4fc; }
        .demo-cred { display: flex; justify-content: space-between; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="login-card">
        <div class="login-logo">
            <div class="logo-icon"><i class="bi bi-bank2"></i></div>
            <h1 class="logo-title">Finance ERP</h1>
            <p class="logo-sub">Loan Management System</p>
        </div>

        @if ($errors->any())
        <div class="alert-danger">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ $errors->first() }}
        </div>
        @endif

        {{-- Demo credentials hint --}}
        <div class="demo-box">
            <div><strong><i class="bi bi-info-circle me-1"></i>Demo Credentials</strong></div>
            <div class="demo-cred"><span>Email:</span> <span>superadmin@financeerp.local</span></div>
            <div class="demo-cred"><span>Password:</span> <span>Admin@12345</span></div>
        </div>

        <form method="POST" action="{{ route('login.post') }}" id="loginForm">
            @csrf

            <div class="mb-4">
                <label class="form-label" for="email">Email Address</label>
                <div class="input-wrapper">
                    <input
                        type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        id="email"
                        name="email"
                        value="{{ old('email', 'superadmin@financeerp.local') }}"
                        placeholder="Enter your email"
                        required
                        autofocus
                    >
                    <i class="bi bi-envelope input-icon"></i>
                </div>
                @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-2">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrapper">
                    <input
                        type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >
                    <i class="bi bi-eye-slash input-icon" id="togglePwd"></i>
                </div>
                @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="remember-row">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <i class="bi bi-shield-lock-fill"></i>
                Sign In Securely
            </button>
        </form>

        <div class="login-footer">
            <p>© {{ date('Y') }} Finance ERP. All rights reserved.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePwd')?.addEventListener('click', function() {
            const pwd = document.getElementById('password');
            const isText = pwd.type === 'text';
            pwd.type = isText ? 'password' : 'text';
            this.className = isText ? 'bi bi-eye-slash input-icon' : 'bi bi-eye input-icon';
        });

        // Loading state on submit
        document.getElementById('loginForm')?.addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing in...';
            btn.disabled = true;
        });
    </script>
</body>
</html>
