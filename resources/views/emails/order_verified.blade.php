<!DOCTYPE html>
<html>
<head>
    <title>Pesanan Terverifikasi</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; line-height: 1.6; background-color: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { width: 100%; background-color: #f4f4f4; padding: 20px 0; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
        /* Header Hijau untuk Lunas */
        .header { background-color: #16a34a; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #f8f9fa; color: #555; font-size: 0.9em; text-transform: uppercase; letter-spacing: 0.5px; }
        th, td { border-bottom: 1px solid #eee; padding: 12px; text-align: left; }
        .total-row { background-color: #f0fdf4; } /* Hijau muda */
        .total-label { font-weight: bold; text-align: right; }
        .total-amount { font-weight: bold; color: #16a34a; font-size: 1.2em; }
        .footer { background-color: #f9fafb; padding: 15px; text-align: center; font-size: 0.8em; color: #888; border-top: 1px solid #eee; }
        .btn-wa { background-color: #25D366; color: white !important; text-decoration: none; padding: 12px 25px; border-radius: 50px; font-weight: bold; display: inline-block; margin-top: 10px; box-shadow: 0 4px 6px rgba(37, 211, 102, 0.2); }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h2 style="margin:0;">Pembayaran Diterima!</h2>
                <p style="margin:5px 0 0 0; opacity: 0.9;">Status: LUNAS ✅</p>
            </div>
            
            <div class="content">
                
                {{-- LOGIKA PESAN CUSTOM DARI ADMIN --}}
                @php
                    $template = $order->batch->mail_verification_message 
                                ?? "Halo {nama_pemesan},\n\nPembayaran Anda untuk {nama_kegiatan} telah kami terima dan berstatus LUNAS ✅.\n\nBerikut rincian pesanan Anda:";
                    
                    // Kita tidak perlu list barang di dalam pesan custom karena sudah ada tabel di bawah
                    $bodyMessage = str_replace(
                        ['{nama_pemesan}', '{nama_kegiatan}', '{detail_pesanan}'],
                        [$order->customer_name, $order->batch->name, ''], // Kosongkan detail_pesanan agar tidak dobel
                        $template
                    );
                @endphp

                <div style="margin-bottom: 20px; white-space: pre-line;">
                    {!! nl2br(e($bodyMessage)) !!}
                </div>



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

                @if(isset($order->batch->whatsapp_link) && $order->batch->whatsapp_link)
                <div style="text-align: center; margin: 25px 0; padding: 20px; background-color: #f0fdf4; border-radius: 10px; border: 1px dashed #25D366;">
                    <p style="margin-bottom: 15px; color: #166534; font-size: 0.9em;">Agar tidak ketinggalan info pengambilan:</p>
                    <a href="{{ $order->batch->whatsapp_link }}" class="btn-wa">
                        Gabung Grup WhatsApp
                    </a>
                </div>
                @endif
                
                {{-- INFO JADWAL PENGAMBILAN --}}
                <div style="margin-top: 25px; padding: 15px; background-color: #eef2ff; border-left: 4px solid #6366f1; border-radius: 4px;">
                    <p style="margin: 0; font-size: 0.9em; color: #4338ca;">
                        <strong>📅 Jadwal Pengambilan:</strong><br>
                        @if($order->batch->pickup_date)
                            {{ \Carbon\Carbon::parse($order->batch->pickup_date)->translatedFormat('l, d F Y') }}
                        @else
                            Akan diinfokan lebih lanjut di Grup WhatsApp.
                        @endif
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