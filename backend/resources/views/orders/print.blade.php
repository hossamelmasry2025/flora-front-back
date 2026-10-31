<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>طلب {{ $order->number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Tahoma, Arial, sans-serif; color: #222; margin: 0; padding: 24px; font-size: 14px; }
        .sheet { max-width: 720px; margin: 0 auto; }
        header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #222; padding-bottom: 12px; margin-bottom: 16px; }
        h1 { margin: 0; font-size: 22px; }
        h2 { font-size: 15px; margin: 0 0 8px; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .box { border: 1px solid #ddd; border-radius: 6px; padding: 12px; }
        p { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        th { background: #f5f5f5; }
        .totals td { border: none; padding: 4px 8px; }
        .card { border: 2px dashed #999; border-radius: 8px; padding: 24px; margin-top: 24px; page-break-before: always; text-align: center; font-size: 20px; line-height: 2; white-space: pre-line; min-height: 200px; display: flex; align-items: center; justify-content: center; }
        .no-print { text-align: center; margin-bottom: 16px; }
        @media print { .no-print { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
<div class="sheet">
    <div class="no-print"><button onclick="window.print()" style="padding:8px 24px;font-size:16px;cursor:pointer">🖨️ طباعة</button></div>

    <header>
        <div>
            <h1>{{ $siteName }}</h1>
            <small>{{ $siteUrl }}</small>
        </div>
        <div style="text-align:left">
            <strong>طلب رقم: {{ $order->number }}</strong><br>
            <small>{{ $order->created_at->format('d/m/Y h:i A') }}</small>
        </div>
    </header>

    <div class="grid">
        <div class="box">
            <h2>المستلم</h2>
            <p><strong>{{ $order->recipient_name }}</strong></p>
            <p>📞 {{ $order->recipient_phone }}</p>
            <p>📍 {{ $order->delivery_zone_name }} — {{ $order->address }}</p>
            <p>🗓️ {{ $order->delivery_date?->format('d/m/Y') ?? 'أقرب وقت' }} {{ $order->delivery_time }}</p>
        </div>
        <div class="box">
            <h2>المرسل</h2>
            <p><strong>{{ $order->sender_name }}</strong> @if($order->hide_sender)<small>(مخفي عن المستلم)</small>@endif</p>
            <p>📞 {{ $order->sender_phone }}</p>
            <p>💳 {{ \App\Models\Order::PAYMENT_METHODS[$order->payment_method] ?? $order->payment_method }}
                — {{ \App\Models\Order::PAYMENT_STATUSES[$order->payment_status] ?? '' }}</p>
        </div>
    </div>

    <table>
        <thead><tr><th>المنتج</th><th>الكمية</th><th>السعر</th><th>الإجمالي</th></tr></thead>
        <tbody>
        @foreach ($order->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->price) }} ج.م</td>
                <td>{{ number_format($item->total) }} ج.م</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals" style="width:auto;margin-right:auto">
        <tr><td>المنتجات:</td><td>{{ number_format($order->subtotal) }} ج.م</td></tr>
        <tr><td>التوصيل:</td><td>{{ number_format($order->delivery_fee) }} ج.م</td></tr>
        <tr><td><strong>الإجمالي:</strong></td><td><strong>{{ number_format($order->total) }} ج.م</strong></td></tr>
        @if ($order->payment_method === 'cod' && $order->payment_status !== 'paid')
            <tr><td colspan="2" style="color:#b00"><strong>يتم تحصيل {{ number_format($order->total) }} ج.م عند الاستلام</strong></td></tr>
        @endif
    </table>

    @if ($order->notes)
        <div class="box"><h2>ملاحظات</h2><p>{{ $order->notes }}</p></div>
    @endif

    @if ($order->card_message)
        {{-- الكارت في صفحة لوحدها عشان يتطبع ويتحط مع البوكيه --}}
        <div class="card">{{ $order->card_message }}</div>
    @endif
</div>
</body>
</html>
