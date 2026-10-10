<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login &mdash; POS System</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e293b 0%, #1a3a6b 50%, #2563eb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, sans-serif;
            padding: 20px;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 24px 64px rgba(0,0,0,0.25);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #1e293b, #2563eb);
            padding: 36px 32px 28px;
            text-align: center;
        }
        .login-icon {
            width: 68px; height: 68px;
            background: rgba(255,255,255,0.15);
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #fff;
            margin-bottom: 14px;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .login-header h1 { color: #fff; font-size: 1.4rem; font-weight: 700; margin: 0; }
        .login-header p  { color: rgba(255,255,255,0.65); font-size: 0.85rem; margin: 4px 0 0; }
        .login-body { padding: 32px; }
        .input-group-text {
            background: #f8fafc; border-right: none; color: #64748b;
        }
        .form-control {
            border-left: none;
        }
        .form-control:focus {
            box-shadow: none; border-color: #2563eb;
        }
        .form-control:focus ~ .input-group-text,
        .input-group:focus-within .input-group-text {
            border-color: #2563eb;
        }
        .btn-login {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none; color: #fff;
            padding: 12px; font-size: 1rem; font-weight: 600;
            border-radius: 10px; width: 100%;
            transition: all 0.2s;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(37,99,235,0.4); }
        .login-footer { padding: 16px 32px; background: #f8fafc; text-align: center; color: #94a3b8; font-size: 0.78rem; border-top: 1px solid #e2e8f0; }
        .show-password { cursor: pointer; background: #f8fafc; border-left: none; }
        .show-password:hover { color: #2563eb; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="login-icon">
                <i class="fas fa-cash-register"></i>
            </div>
            <h1>POS System</h1>
            <p>Point of Sales Management</p>
        </div>

        <div class="login-body">
            <h5 class="mb-1 fw-bold text-dark">Welcome back!</h5>
            <p class="text-muted mb-4" style="font-size:.85rem">Sign in to your account to continue</p>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-start gap-2 mb-4 py-2" style="font-size:.875rem">
                    <i class="fas fa-circle-exclamation mt-1 flex-shrink-0"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" style="font-size:.875rem">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:.875rem">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope" style="font-size:.85rem"></i></span>
                        <input type="email"
                               name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="admin@pos.local"
                               value="{{ old('email') }}"
                               required autofocus autocomplete="email">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:.875rem">Password</label>
                    <div class="input-group" id="passwordGroup">
                        <span class="input-group-text"><i class="fas fa-lock" style="font-size:.85rem"></i></span>
                        <input type="password"
                               name="password"
                               id="passwordInput"
                               class="form-control"
                               placeholder="••••••••"
                               required autocomplete="current-password">
                        <button type="button" class="input-group-text show-password" id="togglePassword" title="Show/hide password">
                            <i class="fas fa-eye" id="eyeIcon" style="font-size:.85rem"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label text-muted" for="remember" style="font-size:.875rem">
                            Remember me
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-right-to-bracket me-2"></i>Sign In
                </button>
            </form>
        </div>

        <div class="login-footer">
            &copy; {{ date('Y') }} POS System &mdash; v1.0.0
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const input = document.getElementById('passwordInput');
            const icon  = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        });
    </script>
</body>
</html>
