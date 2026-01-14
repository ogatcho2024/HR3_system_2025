<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OTP Code</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #1e40af;
            margin: 0;
            font-size: 24px;
        }
        .logo {
            width: 60px;
            height: 60px;
            background-color: #dbeafe;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo svg {
            width: 30px;
            height: 30px;
            fill: #1e40af;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .otp-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 42px;
            font-weight: bold;
            letter-spacing: 8px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
        }
        .otp-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        .expiry-info {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .expiry-info strong {
            color: #92400e;
        }
        .instructions {
            background-color: #f3f4f6;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .instructions h3 {
            margin-top: 0;
            color: #1f2937;
            font-size: 16px;
        }
        .instructions ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .instructions li {
            margin: 8px 0;
            color: #4b5563;
        }
        .security-notice {
            background-color: #fee2e2;
            border-left: 4px solid #dc2626;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #1e40af;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
                </svg>
            </div>
            <h1>Two-Factor Authentication</h1>
        </div>

        <div class="greeting">
            Hello <strong>{{ $userName }}</strong>,
        </div>

        <p>You are receiving this email because a login attempt was made to your CaliCrane HR System account.</p>

        <div class="otp-box">
            <div class="otp-label">YOUR VERIFICATION CODE</div>
            <div class="otp-code">{{ $otp }}</div>
        </div>

        <div class="expiry-info">
            <strong>⏱ Important:</strong> This code will expire in <strong>{{ $expiryMinutes }} minutes</strong> 
            (at {{ \Carbon\Carbon::parse($expiresAt)->format('h:i A') }}).
        </div>

        <div class="instructions">
            <h3>📋 How to use this code:</h3>
            <ul>
                <li>Enter the 6-digit code on the verification page</li>
                <li>Do not share this code with anyone</li>
                <li>Use it only once within the expiration time</li>
                <li>If you didn't request this code, please ignore this email</li>
            </ul>
        </div>

        <div class="security-notice">
            <strong>🔒 Security Notice:</strong> If you did not attempt to log in, please contact your system administrator immediately. Your account security is our top priority.
        </div>

        <div class="footer">
            <p>This is an automated message from CaliCrane HR System.</p>
            <p>Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} CaliCrane. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
