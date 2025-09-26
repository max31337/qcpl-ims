<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QCPL-IMS Invitation</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 30px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .content {
            background: #ffffff;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
        .button {
            display: inline-block;
            background: #1e40af;
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 600;
        }
        .button:hover {
            background: #1d4ed8;
        }
        .footer {
            background: #f9fafb;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 8px 8px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
        }
        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .expiry-notice {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            @php
                $logoPath = public_path('Quezon_City_Public_Library_logo.png');
                $logoExists = file_exists($logoPath);
                $logoBase64 = $logoExists ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
            @endphp
            
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="QCPL" style="width: 60px; height: 60px; border-radius: 50%;">
            @else
                <div style="width: 60px; height: 60px; background: #1e40af; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 18px;">
                    QCPL
                </div>
            @endif
        </div>
        <h1 style="margin: 0; font-size: 24px;">You're Invited!</h1>
        <p style="margin: 10px 0 0; opacity: 0.9;">Join the QCPL Inventory Management System</p>
    </div>

    <div class="content">
        <h2 style="color: #1e40af; margin-top: 0;">Welcome to QCPL-IMS</h2>
        
        <p>Hello,</p>
        
        <p>You have been invited to join the <strong>Quezon City Public Library Inventory Management System</strong>. This system will help you manage assets, supplies, and inventory across our library branches.</p>

        @if($invitation->message)
        <div class="info-box">
            <h4 style="margin-top: 0; color: #1e40af;">Personal Message:</h4>
            <p style="margin-bottom: 0;">{{ $invitation->message }}</p>
        </div>
        @endif

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $registrationUrl }}" class="button">Complete Your Registration</a>
        </div>

        <div class="expiry-notice">
            <strong>⏰ Important:</strong> This invitation expires on <strong>{{ $invitation->expires_at->format('F j, Y \a\t g:i A') }}</strong>. Please complete your registration before this date.
        </div>

        <h3 style="color: #1e40af;">What happens next?</h3>
        <ol>
            <li><strong>Click the registration link</strong> above to access the secure registration form</li>
            <li><strong>Complete your profile</strong> with your personal and organizational details</li>
            <li><strong>Wait for approval</strong> - An administrator will review and approve your account</li>
            <li><strong>Start using QCPL-IMS</strong> once your account is activated</li>
        </ol>

        <div class="info-box">
            <h4 style="margin-top: 0; color: #1e40af;">System Features:</h4>
            <ul style="margin-bottom: 0;">
                <li>Asset tracking and management</li>
                <li>Supply inventory control</li>
                <li>Transfer history and reporting</li>
                <li>Multi-branch organization</li>
                <li>Role-based access control</li>
            </ul>
        </div>

        <p>If you have any questions or need assistance, please contact your system administrator.</p>

        <p>Thank you,<br>
        <strong>QCPL-IMS Administration Team</strong></p>
    </div>

    <div class="footer">
        <p>
            <strong>Quezon City Public Library</strong><br>
            Inventory Management System<br>
            This is an automated message, please do not reply to this email.
        </p>
        <p style="margin-top: 15px; font-size: 12px;">
            If you cannot click the button above, copy and paste this link into your browser:<br>
            <span style="color: #1e40af; word-break: break-all;">{{ $registrationUrl }}</span>
        </p>
    </div>
</body>
</html>