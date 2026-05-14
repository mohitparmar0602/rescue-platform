<!DOCTYPE html>
<html>
<head>
    <title>Agency Approved</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-w: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;">
        <h2 style="color: #2563eb;">Welcome to the Rescue Coordination Platform</h2>
        
        <p>Dear {{ $user->name }},</p>
        
        <p>Great news! Your registration for <strong>{{ $agency->name }}</strong> has been officially approved by our administrative team.</p>
        
        <p>You can now log in to the platform with your email address to begin coordinating rescue efforts, managing resources, and broadcasting live alerts.</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/login') }}" style="background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Log In to Your Dashboard
            </a>
        </div>
        
        <p>Thank you for your commitment to public safety.</p>
        
        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 12px; color: #777;">
            If you did not request this registration, please contact our support team immediately.
        </p>
    </div>
</body>
</html>
