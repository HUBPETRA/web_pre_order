<!DOCTYPE html>
<html>
<head>
    <title>Konfirmasi Pesanan</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; line-height: 1.6; background-color: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { width: 100%; background-color: #f4f4f4; padding: 20px 0; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
        .header { background-color: #2563eb; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #f8f9fa; color: #555; font-size: 0.9em; text-transform: uppercase; letter-spacing: 0.5px; }
        th, td { border-bottom: 1px solid #eee; padding: 12px; text-align: left; }
        .total-row { background-color: #f0f7ff; }
        .total-label { font-weight: bold; text-align: right; }
        .total-amount { font-weight: bold; color: #2563eb; font-size: 1.2em; }
        .footer { background-color: #f9fafb; padding: 15px; text-align: center; font-size: 0.8em; color: #888; border-top: 1px solid #eee; }
        .btn-wa { background-color: #25D366; color: white !important; text-decoration: none; padding: 12px 25px; border-radius: 50px; font-weight: bold; display: inline-block; margin-top: 10px; box-shadow: 0 4px 6px rgba(37, 211, 102, 0.2); }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h2 style="margin:0;">Pesanan Diterima!</h2>
                <p style="margin:5px 0 0 0; opacity: 0.9;">Menunggu Verifikasi</p>
            </div>
            
            <div class="content">
                <p>Halo, <strong>{{ $order->customer_name }}</strong>!</p>
                <p>Terima kasih telah memesan di <strong>{{ $order->batch->name ?? 'PO Genta' }}</strong>. Data pesanan dan bukti pembayaran Anda telah kami terima.</p>

                @if(isset($order->batch->whatsapp_link) && $order->batch->whatsapp_link)
                <div style="text-align: center; margin: 25px 0; padding: 20px; background-color: #f0fdf4; border-radius: 10px; border: 1px dashed #25D366;">
                    <p style="margin-bottom: 15px; color: #166534; font-size: 0.9em;">Agar tidak ketinggalan info pengambilan:</p>
                    <a href="{{ $order->batch->whatsapp_link }}" class="btn-wa">
                        Jangan lupa gabung grup WhatsApp
                    </a>
                </div>
                @endif

                <div style="margin-top: 20px;">
                    <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px; color: #444;">Rincian Pesanan #{{ $order->id }}</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Menu</th>
                                <th style="text-align: center;">Qty</th>
                                <th style="text-align: right;">Harga</th>
                                <th style="text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $total = 0; @endphp
                            @foreach($order->orderItems as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product_name_snapshot }}</strong>
                                </td>
                                <td style="text-align: center;">{{ $item->quantity }}</td>
                                <td style="text-align: right;">Rp {{ number_format($item->price_snapshot, 0, ',', '.') }}</td>
                                <td style="text-align: right;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @php $total += $item->subtotal; @endphp
                            @endforeach
                            
                            <tr class="total-row">
                                <td colspan="3" class="total-label">Total Bayar</td>
                                <td class="total-amount" style="text-align: right;">Rp {{ number_format($total, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 25px; padding: 15px; background-color: #fff8e1; border-left: 4px solid #f59e0b; border-radius: 4px;">
                    <p style="margin: 0; font-size: 0.9em; color: #92400e;">
                        <strong>Status Pembayaran:</strong> Sedang diverifikasi.<br>
                        Anda akan menerima email notifikasi lagi setelah status berubah menjadi <strong>LUNAS</strong>.
                    </p>
                </div>
            </div>

            <div class="footer">
                &copy; {{ date('Y') }} PO Genta<br>
                Email ini dikirim secara otomatis, mohon jangan dibalas.
            </div>
        </div>
    </div>
</body>
</html>