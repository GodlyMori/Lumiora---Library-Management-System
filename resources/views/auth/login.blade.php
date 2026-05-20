<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Lumiora</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        /* ===== BACKGROUND ===== */
        body {
            background: url('/images/bg-library.png') no-repeat center center/cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            font-family: 'Segoe UI', sans-serif;
        }

        /* Dark overlay */
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

        /* ===== LOGIN CARD ===== */
        .login-card {
            border: none;
            border-radius: 18px;
            padding: 2rem;

            /* Glass effect */
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);

            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);

            color: #fff;

            animation: fadeInUp 0.6s ease;
        }

        /* ===== TEXT ===== */
        .login-card h4 {
            color: #ffffff;
        }

        .login-card p {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-label {
            color: #e6eefc;
        }

        /* ===== INPUTS ===== */
        .form-control {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            border-radius: 10px;
        }

        .form-control::placeholder {
            color: rgba(255,255,255,0.5);
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: #4da3ff;
            box-shadow: 0 0 0 0.2rem rgba(77, 163, 255, 0.25);
            color: #fff;
        }

        /* ===== BUTTON ===== */
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

        /* ===== ICON ===== */
        .login-icon {
            font-size: 2.5rem;
            color: #60a5fa;
        }

        /* ===== INFO BOX ===== */
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

        /* ===== ANIMATION ===== */
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
        <div class="col-md-4 col-sm-8">
            <div class="card login-card">

                <div class="text-center mb-4">
                    <img src="/images/cropped-logo.png" alt="Lumiora Logo" class="mb-2" style="width: 190px;">
                    <p class="small">Sign in to your account</p>
                </div>

                <div class="info-box">
                    <i class="bi bi-info-circle me-2"></i>
                    Enter your email to receive a verification code
                </div>

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('login.request-code') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               placeholder="your.email@example.com"
                               required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-envelope"></i> Send Verification Code
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>