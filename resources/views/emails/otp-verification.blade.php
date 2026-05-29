<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f5f5f5; padding: 20px; border-radius: 8px;">
    <div style="background: white; padding: 30px; border-radius: 8px; text-align: center;">
        <h2 style="color: #333; margin-bottom: 10px;">Password Reset Request</h2>
        <p style="color: #666; margin-bottom: 30px;">We received a request to reset your PokeTrade TCG password.</p>

        <div style="background: #f0f0f0; padding: 20px; border-radius: 8px; margin: 30px 0;">
            <p style="color: #666; font-size: 14px; margin: 0 0 10px 0;">Your verification code is:</p>
            <h1 style="color: #7c3aed; font-size: 48px; letter-spacing: 5px; margin: 10px 0; font-weight: bold;">
                {{ $otp }}
            </h1>
            <p style="color: #999; font-size: 12px; margin: 10px 0 0 0;">This code expires in {{ $expiresIn }} minutes</p>
        </div>

        <p style="color: #666; font-size: 14px; margin: 20px 0;">
            Enter this code on the verification page to reset your password.
        </p>

        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">

        <p style="color: #999; font-size: 12px;">
            If you didn't request this, please ignore this email. Your account is safe.
        </p>
    </div>
</div>
