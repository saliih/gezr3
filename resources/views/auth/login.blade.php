<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - GEZR</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e2a3a 0%, #2c3e50 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-logo .icon {
            width: 64px;
            height: 64px;
            background: #3498db;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #fff;
            margin-bottom: 0.75rem;
        }
        .login-logo h4 { font-weight: 700; color: #1e2a3a; margin: 0; }
        .login-logo p  { color: #7f8c9a; font-size: 0.85rem; margin: 0; }
        .form-label { font-size: 0.85rem; font-weight: 600; color: #374151; }
        .form-control { border-radius: 8px; }
        .btn-login {
            background: #3498db;
            border: none;
            border-radius: 8px;
            padding: 0.65rem;
            font-weight: 600;
            width: 100%;
        }
        .btn-login:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <div class="icon"><i class="bi bi-droplet-fill"></i></div>
            <h4>GEZR</h4>
            <p>نظام إدارة المياه</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-sm">
                <i class="bi bi-exclamation-triangle me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">اسم المستخدم</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                           value="{{ old('username') }}" autofocus autocomplete="username">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">كلمة المرور</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" autocomplete="current-password">
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-login">
                <i class="bi bi-box-arrow-in-right me-1"></i> دخول
            </button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
