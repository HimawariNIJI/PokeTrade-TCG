@php
    $isVerification = $type === 'email_verification';
    $eyebrow = $isVerification ? 'Email verification' : 'Password reset';
    $heading = $isVerification ? 'Verify your email address' : 'Your verification code';
    $intro = $isVerification
        ? 'Welcome to ' . config('app.name') . '! Use the code below to verify your email address.'
        : 'We received a request to reset your ' . config('app.name') . ' password. Use the code below to continue.';
    $footer = $isVerification
        ? 'Enter this code on the email verification page to complete registration.'
        : 'Enter this code on the verification page to finish resetting your password.';
@endphp

@component('emails._layout', [
    'eyebrow' => $eyebrow,
    'heading' => $heading,
])
    <p style="margin:0 0 16px;font-size:16px;line-height:1.6;">Hey trainer,</p>

    <p style="margin:0 0 24px;font-size:16px;line-height:1.6;">
        {{ $intro }}
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin:0 0 24px;background:linear-gradient(135deg,#faf5ff 0%,#fdf2f8 50%,#fffbeb 100%);border:1px solid rgba(124,58,237,0.18);border-radius:16px;">
        <tr>
            <td style="padding:24px 20px;text-align:center;">
                <p style="margin:0 0 8px;font-size:12px;color:#7c3aed;letter-spacing:2px;text-transform:uppercase;font-weight:700;">Your verification code</p>
                <p style="margin:0;font-size:44px;font-weight:700;letter-spacing:10px;color:#1a1a1a;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;text-shadow:0 2px 4px rgba(124,58,237,0.15);">
                    {{ $otp }}
                </p>
                <p style="margin:12px 0 0;font-size:12px;color:#999;">Expires in {{ $expiresIn }} minutes</p>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 24px;font-size:14px;color:#555;line-height:1.6;">
        {{ $footer }}
        If you didn't request this, you can safely ignore this email — your account stays locked tight.
    </p>
@endcomponent
