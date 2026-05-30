@component('emails._layout', [
    'eyebrow' => 'Auction won',
    'heading' => 'You caught it! 🎉',
])
    <p style="margin:0 0 20px;font-size:16px;line-height:1.6;color:#1a1a1a;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
        Congrats <strong style="color:#7c3aed;">{{ $user->name }}</strong> — you're the winning bidder!
    </p>

    {{-- Hero card display with confetti/sparkles --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;background:linear-gradient(135deg,#fef3c7 0%,#fce7f3 50%,#ede9fe 100%);border-radius:16px;padding:28px 24px;border:1px solid rgba(245,158,11,0.2);">
        <tr>
            <td align="center">
                <p style="margin:0 0 12px;font-size:24px;letter-spacing:8px;">✨ 🏆 ✨</p>
                @if($auction->card->image_small ?? null)
                    <img src="{{ $auction->card->image_small }}" alt="{{ $cardName }}" width="200" style="display:block;border-radius:14px;box-shadow:0 12px 32px rgba(245,158,11,0.35), 0 0 0 4px #ffffff;max-width:200px;height:auto;margin:0 auto;">
                @endif
                <p style="margin:18px 0 4px;font-size:24px;font-weight:700;color:#1a1a1a;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
                    {{ $cardName }}
                </p>
                <p style="margin:0;font-size:12px;color:#92400e;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;letter-spacing:1.5px;text-transform:uppercase;font-weight:600;">
                    📦 Heading your way
                </p>
            </td>
        </tr>
    </table>

    {{-- Winning bid badge --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;background:#1a1a1a;border-radius:16px;padding:20px;">
        <tr>
            <td align="center">
                <p style="margin:0;font-size:11px;color:rgba(255,255,255,0.7);letter-spacing:2px;text-transform:uppercase;font-weight:600;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
                    Final winning bid
                </p>
                <p style="margin:8px 0 0;font-size:32px;font-weight:700;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;background:linear-gradient(135deg,#fbbf24 0%,#ec4899 50%,#a78bfa 100%);-webkit-background-clip:text;background-clip:text;color:#fbbf24;">
                    Rp {{ number_format((float) $amount, 0, ',', '.') }}
                </p>
            </td>
        </tr>
    </table>

    @isset($order)
        <p style="margin:0 0 16px;font-size:14px;color:#555;line-height:1.6;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
            Your order <strong style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace;background:#faf5ff;padding:2px 8px;border-radius:6px;color:#7c3aed;">{{ $order->code }}</strong>
            has been created and is ready to ship. We'll follow up with shipping confirmation once it's on the way.
        </p>
    @endisset

    <p style="margin:0 0 24px;font-size:13px;color:#888;line-height:1.6;text-align:center;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
        ⓘ If something isn't right, you have a 7-day window to open a refund request from the auction page.
    </p>

    {{-- CTA --}}
    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:8px auto 0;">
        <tr>
            <td style="border-radius:999px;background:linear-gradient(135deg,#f59e0b 0%,#ec4899 100%);box-shadow:0 6px 16px rgba(236,72,153,0.3);">
                <a href="{{ $actionUrl }}" style="display:inline-block;padding:16px 36px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;letter-spacing:0.5px;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
                    @isset($order) View order → @else View auction → @endisset
                </a>
            </td>
        </tr>
    </table>
@endcomponent
