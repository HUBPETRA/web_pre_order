<!DOCTYPE html>
<html>
<head>
    <title>Reminder Pengambilan</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; line-height: 1.6; background-color: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { width: 100%; background-color: #f4f4f4; padding: 20px 0; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
        
        /* HEADER ORANYE (REMINDER) */
        .header { background-color: #f97316; color: white; padding: 20px; text-align: center; }
        
        .content { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #fff7ed; color: #9a3412; font-size: 0.9em; text-transform: uppercase; letter-spacing: 0.5px; }
        th, td { border-bottom: 1px solid #fed7aa; padding: 12px; text-align: left; } /* Garis Oranye Muda */
        .total-row { background-color: #fff7ed; }
        .total-label { font-weight: bold; text-align: right; }
        .total-amount { font-weight: bold; color: #c2410c; font-size: 1.2em; }
        
        .footer { background-color: #f9fafb; padding: 15px; text-align: center; font-size: 0.8em; color: #888; border-top: 1px solid #eee; }
        
        /* Tombol WA (Hijau) */
        .btn-wa { background-color: #25D366; color: white !important; text-decoration: none; padding: 12px 25px; border-radius: 50px; font-weight: bold; display: inline-block; margin-top: 10px; box-shadow: 0 4px 6px rgba(37, 211, 102, 0.2); }
        
        /* Box Info Penting */
        .info-box { margin-top: 25px; padding: 15px; background-color: #fff7ed; border-left: 4px solid #f97316; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            {{-- HEADER --}}
            <div class="header">
                <h2 style="margin:0;">🔔 Reminder Pengambilan</h2>
                <p style="margin:5px 0 0 0; opacity: 0.9;">Jadwal: BESOK</p>
            </div>
            
            <div class="content">
                <p>Halo, <strong>{{ $order->customer_name }}</strong>!</p>
                <p>Ini adalah pengingat otomatis bahwa pesanan PO <strong>{{ $order->batch->name }}</strong> Anda sudah siap untuk diambil besok.</p>

                {{-- INFO WAKTU & LOKASI (Highlight) --}}
                <div class="info-box">
                    <h3 style="margin: 0 0 10px 0; color: #c2410c; font-size: 16px;">📍 Detail Pengambilan:</h3>
                    <p style="margin: 5px 0; font-size: 0.95em; color: #7c2d12;">
                        <strong>📅 Tanggal:</strong><br>
                        {{ \Carbon\Carbon::parse($order->batch->pickup_date)->translatedFormat('l, d F Y') }}
                    </p>
                    <p style="margin: 10px 0 0 0; font-size: 0.95em; color: #7c2d12;">
                        <strong>🏢 Lokasi:</strong><br>
                        {{ $order->batch->pickup_location ?? 'Cek info di Grup WhatsApp' }}
                    </p>
                </div>

                <p style="margin-top: 20px; font-size: 0.9em; color: #666;">
                    Mohon datang tepat waktu dan tunjukkan email ini (atau bukti transfer) saat pengambilan.
                </p>

                {{-- TOMBOL WA --}}
                @if(isset($order->batch->whatsapp_link) && $order->batch->whatsapp_link)
                <div style="text-align: center; margin: 25px 0; padding: 20px; background-color: #f0fdf4; border-radius: 10px; border: 1px dashed #25D366;">
                    <p style="margin-bottom: 15px; color: #166534; font-size: 0.9em;">Butuh koordinasi lebih lanjut?</p>
                    <a href="{{ $order->batch->whatsapp_link }}" class="btn-wa">
                        Cek Grup WhatsApp
                    </a>
                </div>
                @endif

                {{-- RINCIAN PESANAN (Opsional, tapi bagus untuk mengingatkan user apa yang harus diambil) --}}
                <div style="margin-top: 30px;">
                    <h3 style="border-bottom: 2px solid #fed7aa; padding-bottom: 10px; color: #444; font-size: 16px;">
                        📦 Barang yang harus diambil:
                    </h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Menu</th>
                                <th style="text-align: center;">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->orderItems as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product_name_snapshot }}</strong>
                                </td>
                                <td style="text-align: center;">{{ $item->quantity }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
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