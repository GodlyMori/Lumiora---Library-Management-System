<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            font-size: 15px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .code-box {
            background: linear-gradient(135deg, #e0e7ff, #ede9fe);
            border: 2px dashed #6366f1;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
        }
        .code {
            font-size: 36px;
            font-weight: bold;
            color: #6366f1;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .code-label {
            font-size: 13px;
            color: #8b5cf6;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 25px 0;
            text-align: left;
            border-radius: 6px;
        }
        .warning-title {
            font-weight: bold;
            color: #92400e;
            margin-bottom: 5px;
        }
        .warning-text {
            font-size: 14px;
            color: #78350f;
            margin: 0;
        }
        .footer {
            background: #f8fafc;
            padding: 20px 30px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .footer a {
            color: #6366f1;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🔐 Lumiora Library</h1>
        </div>
        
        <div class="content">
            <div class="greeting">
                Hello {{ $userName }}! 👋
            </div>
            
            <div class="message">
                You requested to log in to your Lumiora Library account. Please use the verification code below to complete your login.
            </div>
            
            <div class="code-box">
                <div class="code">{{ $code }}</div>
                <div class="code-label">Your Verification Code</div>
            </div>
            
            <div class="message">
                This code will expire in <strong>10 minutes</strong>. Enter it on the login page to access your account.
            </div>
            
            <div class="warning">
                <div class="warning-title">⚠️ Security Notice</div>
                <p class="warning-text">
                    If you didn't request this code, please ignore this email. Someone may have entered your email address by mistake. Your account remains secure.
                </p>
            </div>
        </div>
        
        <div class="footer">
            <p>This is an automated message from Lumiora Library Management System.</p>
            <p>Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
