@component('emails._layout', [
    'eyebrow' => 'Order confirmed',
    'heading' => 'Thanks, trainer!',
])
    <p style="margin:0 0 20px;font-size:16px;line-height:1.6;color:#1a1a1a;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
        Hey <strong style="color:#7c3aed;">{{ $user->name }}</strong> — payment received for order
        <strong style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace;background:#faf5ff;padding:2px 8px;border-radius:6px;color:#7c3aed;font-size:14px;">{{ $order->code }}</strong>.
        We'll let you know as soon as it ships.
    </p>

    {{-- Item list with product thumbnails --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;border:1px solid rgba(124,58,237,0.12);border-radius:16px;overflow:hidden;background:linear-gradient(180deg,#faf5ff 0%,#ffffff 80px);">
        <tr>
            <td style="padding:14px 20px;background:linear-gradient(135deg,#7c3aed 0%,#ec4899 100%);">
                <p style="margin:0;font-size:12px;color:#ffffff;letter-spacing:1.5px;text-transform:uppercase;font-weight:600;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
                    📦 In this haul
                </p>
            </td>
        </tr>
        @foreach($order->items as $item)
            @php
                $imgUrl = $item->image_snapshot
                    ? (str_starts_with($item->image_snapshot, 'http') ? $item->image_snapshot : asset('storage/' . $item->image_snapshot))
                    : null;
            @endphp
            <tr>
                <td style="padding:14px 20px;border-bottom:1px solid rgba(124,58,237,0.08);">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="64" valign="top" style="padding-right:14px;">
                                @if($imgUrl)
                                    <img src="{{ $imgUrl }}" alt="{{ $item->name_snapshot }}" width="56" height="56" style="display:block;width:56px;height:56px;border-radius:10px;object-fit:cover;border:1px solid rgba(124,58,237,0.15);background:#faf5ff;">
                                @else
                                    <div style="width:56px;height:56px;border-radius:10px;background:#faf5ff;border:1px solid rgba(124,58,237,0.15);text-align:center;line-height:56px;font-size:22px;">◇</div>
                                @endif
                            </td>
                            <td valign="middle" style="font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
                                <p style="margin:0;font-size:15px;font-weight:600;color:#1a1a1a;line-height:1.3;">{{ $item->name_snapshot }}</p>
                                <p style="margin:4px 0 0;font-size:12px;color:#888;">Qty {{ $item->quantity }} · Rp {{ number_format((float) $item->price_snapshot, 0, ',', '.') }} ea</p>
                            </td>
                            <td valign="middle" align="right" style="font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;font-size:15px;font-weight:700;color:#1a1a1a;white-space:nowrap;padding-left:8px;">
                                Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        @endforeach
    </table>

    {{-- Totals --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
        <tr>
            <td style="padding:6px 16px;font-size:13px;color:#666;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">Subtotal</td>
            <td style="padding:6px 16px;font-size:13px;color:#666;text-align:right;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding:6px 16px;font-size:13px;color:#666;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">Shipping</td>
            <td style="padding:6px 16px;font-size:13px;color:#666;text-align:right;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">Rp {{ number_format((float) $order->shipping_fee, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding:6px 16px 12px;font-size:13px;color:#666;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">Tax</td>
            <td style="padding:6px 16px 12px;font-size:13px;color:#666;text-align:right;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">Rp {{ number_format((float) $order->tax, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding:16px;font-size:18px;font-weight:700;color:#ffffff;background:linear-gradient(135deg,#7c3aed 0%,#ec4899 100%);border-radius:12px 0 0 12px;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">Total</td>
            <td style="padding:16px;font-size:20px;font-weight:700;color:#ffffff;background:linear-gradient(135deg,#7c3aed 0%,#ec4899 100%);text-align:right;border-radius:0 12px 12px 0;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- Shipping --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;background:#faf5ff;border-radius:12px;padding:18px 20px;">
        <tr>
            <td style="font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
                <p style="margin:0 0 6px;font-size:11px;color:#7c3aed;letter-spacing:1.5px;text-transform:uppercase;font-weight:600;">🚚 Shipping to</p>
                <p style="margin:0;font-size:14px;color:#1a1a1a;line-height:1.6;">
                    <strong>{{ $order->shipping_name }}</strong><br>
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_city }} {{ $order->shipping_postal_code }}<br>
                    <span style="color:#888;">{{ $order->shipping_phone }}</span>
                </p>
            </td>
        </tr>
    </table>

    {{-- CTA --}}
    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:8px auto 0;">
        <tr>
            <td style="border-radius:999px;background:linear-gradient(135deg,#7c3aed 0%,#ec4899 100%);box-shadow:0 6px 16px rgba(124,58,237,0.3);">
                <a href="{{ $actionUrl }}" style="display:inline-block;padding:16px 36px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;letter-spacing:0.5px;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
                    View order →
                </a>
            </td>
        </tr>
    </table>
@endcomponent
