@component('emails._layout', [
    'eyebrow' => 'Wishlist alert',
    'heading' => 'A card you want is live!',
])
    <p style="margin:0 0 20px;font-size:16px;line-height:1.6;color:#1a1a1a;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
        Hey <strong style="color:#7c3aed;">{{ $user->name }}</strong>, great news —
    </p>

    {{-- Hero card display with the actual TCG art --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;background:linear-gradient(135deg,#faf5ff 0%,#fdf2f8 100%);border-radius:16px;padding:24px;border:1px solid rgba(124,58,237,0.12);">
        <tr>
            <td align="center">
                @if($auction->card->image_small ?? null)
                    <img src="{{ $auction->card->image_small }}" alt="{{ $cardName }}" width="180" style="display:block;border-radius:12px;box-shadow:0 8px 24px rgba(124,58,237,0.25);max-width:180px;height:auto;">
                @endif
                <p style="margin:18px 0 4px;font-size:22px;font-weight:700;color:#1a1a1a;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
                    {{ $cardName }}
                </p>
                <p style="margin:0;font-size:13px;color:#888;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;letter-spacing:0.5px;text-transform:uppercase;font-weight:500;">
                    is now on auction
                </p>
            </td>
        </tr>
    </table>

    {{-- Bid info card --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
        <tr>
            <td width="50%" style="padding:14px;background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;margin-right:8px;text-align:center;" valign="top">
                <p style="margin:0;font-size:11px;color:#9a3412;letter-spacing:1px;text-transform:uppercase;font-weight:600;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">Starting bid</p>
                <p style="margin:6px 0 0;font-size:20px;font-weight:700;color:#1a1a1a;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
                    Rp {{ number_format((float) $auction->starting_bid, 0, ',', '.') }}
                </p>
            </td>
            <td width="8"></td>
            @if($auction->buy_now_price)
                <td width="50%" style="padding:14px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:12px;text-align:center;" valign="top">
                    <p style="margin:0;font-size:11px;color:#065f46;letter-spacing:1px;text-transform:uppercase;font-weight:600;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">Buy now</p>
                    <p style="margin:6px 0 0;font-size:20px;font-weight:700;color:#1a1a1a;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
                        Rp {{ number_format((float) $auction->buy_now_price, 0, ',', '.') }}
                    </p>
                </td>
            @else
                <td width="50%" style="padding:14px;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:12px;text-align:center;" valign="top">
                    <p style="margin:0;font-size:11px;color:#6b7280;letter-spacing:1px;text-transform:uppercase;font-weight:600;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">Ends</p>
                    <p style="margin:6px 0 0;font-size:14px;font-weight:600;color:#1a1a1a;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
                        {{ $auction->ends_at->format('M j, g:i A') }}
                    </p>
                </td>
            @endif
        </tr>
    </table>

    @if($auction->buy_now_price)
        <p style="margin:0 0 24px;font-size:13px;color:#666;line-height:1.6;text-align:center;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
            ⏰ Auction ends {{ $auction->ends_at->format('M j, Y \a\t g:i A') }}
        </p>
    @endif

    {{-- CTA --}}
    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:8px auto 0;">
        <tr>
            <td style="border-radius:999px;background:linear-gradient(135deg,#7c3aed 0%,#ec4899 100%);box-shadow:0 6px 16px rgba(124,58,237,0.3);">
                <a href="{{ $actionUrl }}" style="display:inline-block;padding:16px 36px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;letter-spacing:0.5px;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
                    View auction →
                </a>
            </td>
        </tr>
    </table>
@endcomponent
