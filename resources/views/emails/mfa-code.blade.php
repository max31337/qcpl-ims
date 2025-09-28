<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $mfaCode->purpose === 'login' ? 'Login Verification' : 'Security Verification' }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #f0f0f0; }
        .logo { font-size: 24px; font-weight: bold; color: #2563eb; }
        .content { padding: 30px 0; }
        .code-box { background-color: #f8fafc; border: 2px dashed #e2e8f0; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0; }
        .code { font-size: 32px; font-weight: bold; color: #1e40af; letter-spacing: 4px; font-family: 'Courier New', monospace; }
        .footer { text-align: center; padding: 20px 0; border-top: 1px solid #f0f0f0; color: #6b7280; font-size: 14px; }
        .warning { background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">QCPL-IMS</div>
            <p>Quezon City Public Library - Inventory Management</p>
        </div>

        <div class="content">
            <h2>Hello, {{ $user->name }}!</h2>
            
            @if($mfaCode->purpose === 'login')
                <p>We received a request to sign in to your account. Please use the verification code below to complete your login:</p>
            @elseif($mfaCode->purpose === 'password_change')
                <p>We received a request to change your password. Please use the verification code below to confirm this action:</p>
            @else
                <p>Please use the verification code below to complete your security verification:</p>
            @endif

            <div class="code-box">
                <div class="code">{{ $mfaCode->code }}</div>
                <p style="margin: 10px 0 0 0; color: #6b7280;">This code expires in 10 minutes</p>
            </div>

            <div class="warning">
                <strong>Security Notice:</strong> If you didn't request this code, please ignore this email and consider changing your password. Never share this code with anyone.
            </div>

            <p>For your security, this code will expire at <strong>{{ $mfaCode->expires_at->format('M d, Y \a\t g:i A') }}</strong>.</p>
        </div>

        <div class="footer">
            <p>This is an automated message from QCPL-IMS. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} Quezon City Public Library. All rights reserved.</p>
        </div>
    </div>
</body>
</html>