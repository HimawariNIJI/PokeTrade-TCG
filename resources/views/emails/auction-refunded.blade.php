@component('emails._layout', [
    'eyebrow' => 'Auction refund',
    'heading' => 'Your bid was refunded.',
])
    <p style="margin:0 0 16px;font-size:16px;line-height:1.6;">Hey {{ $user->name }},</p>

    <p style="margin:0 0 16px;font-size:16px;line-height:1.6;">
        The auction for <strong>{{ $cardName }}</strong> has ended and you weren't the winning bidder.
        Your bid has been refunded in full to your original payment method.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 24px;background:#fdf2f8;border:1px solid #fbcfe8;border-radius:12px;padding:16px 20px;">
        <tr>
            <td style="font-size:13px;color:#666;letter-spacing:1px;text-transform:uppercase;font-weight:700;">Refunded amount</td>
        </tr>
        <tr>
            <td style="font-size:28px;font-weight:800;color:#db2777;padding-top:4px;">
                Rp {{ number_format((float) $amount, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <p style="margin:0 0 24px;font-size:14px;color:#555;line-height:1.6;">
        Refunds typically arrive within 3-7 business days depending on your bank or e-wallet.
        Better luck on the next one!
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:8px 0 0;">
        <tr>
            <td style="border-radius:999px;background:#1a1a1a;">
                <a href="{{ $actionUrl }}" style="display:inline-block;padding:14px 28px;font-size:14px;font-weight:700;color:#ffffff;text-decoration:none;letter-spacing:0.5px;">
                    Browse auctions →
                </a>
            </td>
        </tr>
    </table>
@endcomponent
