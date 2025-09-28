<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Password Changed</title>
    <style>
        .container { max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; }
        .header { background: #dc2626; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .alert { background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .success { background: #d1fae5; border: 1px solid #10b981; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .details { background: #f8fafc; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .footer { background: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #64748b; }
        .btn { background: #dc2626; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔒 Security Alert</h1>
            <p>Your QCPL-IMS Password Has Been Changed</p>
        </div>
        
        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>
            
            <div class="success">
                <strong>✅ Password Changed Successfully:</strong> Your QCPL-IMS account password has been updated.
            </div>
            
            <p>Your account password was successfully changed with the following details:</p>
            
            <div class="details">
                <h3>Change Details:</h3>
                <ul>
                    <li><strong>Date & Time:</strong> {{ $changeDetails['timestamp'] ?? now()->format('F j, Y \a\t g:i A') }}</li>
                    <li><strong>IP Address:</strong> {{ $changeDetails['ip_address'] ?? 'Unknown' }}</li>
                    <li><strong>Browser:</strong> {{ $changeDetails['browser'] ?? 'Unknown' }} {{ $changeDetails['browser_version'] ?? '' }}</li>
                    <li><strong>Operating System:</strong> {{ $changeDetails['platform'] ?? 'Unknown' }}</li>
                    <li><strong>Device Type:</strong> {{ ucfirst($changeDetails['device'] ?? 'Unknown') }}</li>
                    @if(isset($changeDetails['changed_by']))
                    <li><strong>Changed By:</strong> {{ $changeDetails['changed_by'] }}</li>
                    @endif
                </ul>
            </div>
            
            <p><strong>Was this you?</strong></p>
            <ul>
                <li>✅ <strong>If you made this change:</strong> No action needed. Your account is secure.</li>
                <li>❌ <strong>If you didn't make this change:</strong> Your account may be compromised. Please contact your system administrator immediately.</li>
            </ul>
            
            <div class="alert">
                <strong>⚠️ Important Security Notice:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>This password change affects access to your QCPL-IMS account</li>
                    <li>You will need to use your new password for future logins</li>
                    <li>All active sessions have been logged out for security</li>
                    <li>If you didn't make this change, contact support immediately</li>
                </ul>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('login') }}" class="btn">Login to Your Account</a>
            </div>
            
            <div class="success">
                <strong>🛡️ Security Recommendations:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Enable Multi-Factor Authentication (MFA) for enhanced security</li>
                    <li>Use a unique password that you don't use anywhere else</li>
                    <li>Consider using a password manager</li>
                    <li>Regularly review your account activity</li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p>This is an automated security notification from the Quezon City Public Library Inventory Management System.</p>
            <p>If you did not make this change or have concerns, please contact your system administrator immediately.</p>
            <p style="margin-top: 15px; font-size: 10px;">
                This email was sent to {{ $user->email }} for security purposes. 
                You cannot unsubscribe from security notifications.
            </p>
        </div>
    </div>
</body>
</html>