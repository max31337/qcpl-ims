<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login Notification</title>
    <style>
        .container { max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; }
        .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .alert { background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .details { background: #f8fafc; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .footer { background: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #64748b; }
        .btn { background: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Security Alert</h1>
            <p>New Login to Your QCPL-IMS Account</p>
        </div>
        
        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>
            
            <div class="alert">
                <strong>⚠️ Security Notice:</strong> We detected a new login to your QCPL-IMS account.
            </div>
            
            <p>A successful login was made to your account with the following details:</p>
            
            <div class="details">
                <h3>Login Details:</h3>
                <ul>
                    <li><strong>Date & Time:</strong> {{ $loginDetails['timestamp'] ?? now()->format('F j, Y \a\t g:i A') }}</li>
                    <li><strong>IP Address:</strong> {{ $loginDetails['ip_address'] ?? 'Unknown' }}</li>
                    <li><strong>Browser:</strong> {{ $loginDetails['browser'] ?? 'Unknown' }} {{ $loginDetails['browser_version'] ?? '' }}</li>
                    <li><strong>Operating System:</strong> {{ $loginDetails['platform'] ?? 'Unknown' }}</li>
                    <li><strong>Device Type:</strong> {{ ucfirst($loginDetails['device'] ?? 'Unknown') }}</li>
                    @if(isset($loginDetails['location']))
                    <li><strong>Location:</strong> {{ $loginDetails['location'] }}</li>
                    @endif
                </ul>
            </div>
            
            <p><strong>Was this you?</strong></p>
            <ul>
                <li>✅ <strong>If this was you:</strong> No action needed. You can safely ignore this email.</li>
                <li>❌ <strong>If this wasn't you:</strong> Your account may be compromised. Please change your password immediately and contact your system administrator.</li>
            </ul>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('profile') }}" class="btn">Secure Your Account</a>
            </div>
            
            <div class="alert">
                <strong>Security Tips:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Always log out from shared computers</li>
                    <li>Use strong, unique passwords</li>
                    <li>Enable Multi-Factor Authentication (MFA) for extra security</li>
                    <li>Report suspicious activity immediately</li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p>This is an automated security notification from the Quezon City Public Library Inventory Management System.</p>
            <p>If you have questions, please contact your system administrator.</p>
            <p style="margin-top: 15px; font-size: 10px;">
                This email was sent to {{ $user->email }} for security purposes. 
                You cannot unsubscribe from security notifications.
            </p>
        </div>
    </div>
</body>
</html>