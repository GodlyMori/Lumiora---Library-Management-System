<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code – Lumiora</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            background: url('/images/bg-library.png') no-repeat center center/cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            font-family: 'Segoe UI', sans-serif;
        }

        body::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 1;
        }

        .verify-card {
            border: none;
            border-radius: 18px;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            color: #fff;
            animation: fadeInUp 0.6s ease;
        }

        .verify-card h4 {
            color: #ffffff;
        }

        .verify-card p {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-label {
            color: #e6eefc;
        }

        .code-input {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            border-radius: 10px;
            text-align: center;
            font-size: 24px;
            letter-spacing: 8px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }

        .code-input::placeholder {
            color: rgba(255,255,255,0.3);
            letter-spacing: 4px;
        }

        .code-input:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: #4da3ff;
            box-shadow: 0 0 0 0.2rem rgba(77, 163, 255, 0.25);
            color: #fff;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border: none;
            border-radius: 10px;
            transition: 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }

        .btn-link {
            color: #60a5fa;
            text-decoration: none;
        }

        .btn-link:hover {
            color: #93c5fd;
            text-decoration: underline;
        }

        .info-box {
            background: rgba(59, 130, 246, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.85);
        }

        .info-box i {
            color: #60a5fa;
        }

        .email-badge {
            background: rgba(139, 92, 246, 0.2);
            border: 1px solid rgba(139, 92, 246, 0.4);
            color: #c4b5fd;
            padding: 8px 16px;
            border-radius: 8px;
            display: inline-block;
            font-size: 14px;
            margin: 10px 0;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(25px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-sm-8">
            <div class="card verify-card">

                <div class="text-center mb-4">
                    <div style="font-size: 48px; margin-bottom: 10px;">📧</div>
                    <h4>Check your email</h4>
                    <p class="small">We've sent a verification code to</p>
                    <div class="email-badge">
                        <i class="bi bi-envelope-fill me-2"></i>{{ session('verification_email') }}
                    </div>
                </div>

                <div class="info-box">
                    <i class="bi bi-clock me-2"></i>
                    Code expires in 10 minutes
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            {{ $error }}
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login.verify') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label">Enter 6-digit code</label>
                        <input type="text" 
                               name="code" 
                               class="form-control code-input @error('code') is-invalid @enderror"
                               placeholder="000000"
                               maxlength="6"
                               pattern="[0-9]{6}"
                               inputmode="numeric"
                               required 
                               autofocus
                               autocomplete="off">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-check-circle"></i> Verify & Login
                    </button>
                </form>

                <div class="text-center">
                    <p class="small mb-2" style="color: rgba(255, 255, 255, 0.6);">
                        Didn't receive the code?
                    </p>
                    <form method="POST" action="{{ route('login.resend-code') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-link btn-sm">
                            <i class="bi bi-arrow-clockwise"></i> Resend Code
                        </button>
                    </form>
                    <span style="color: rgba(255, 255, 255, 0.4);">|</span>
                    <a href="{{ route('login') }}" class="btn btn-link btn-sm">
                        <i class="bi bi-arrow-left"></i> Use Different Email
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto-submit when 6 digits are entered
    const codeInput = document.querySelector('input[name="code"]');
    codeInput.addEventListener('input', function(e) {
        // Only allow numbers
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Auto-submit when 6 digits
        if (this.value.length === 6) {
            this.form.submit();
        }
    });

    // Prevent paste of non-numeric
    codeInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const pasteData = (e.clipboardData || window.clipboardData).getData('text');
        const numericOnly = pasteData.replace(/[^0-9]/g, '').slice(0, 6);
        this.value = numericOnly;
        
        if (numericOnly.length === 6) {
            this.form.submit();
        }
    });
</script>
</body>
</html>
