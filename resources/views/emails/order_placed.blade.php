<!DOCTYPE html>
<html>
<head>
    <title>Konfirmasi Pesanan</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px; }
        .header { background-color: #2563eb; color: white; padding: 15px; text-align: center; border-radius: 8px 8px 0 0; }
        .details { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border-bottom: 1px solid #eee; padding: 10px; text-align: left; }
        .total { font-weight: bold; font-size: 1.2em; color: #2563eb; }
        .footer { margin-top: 30px; font-size: 0.8em; text-align: center; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Pesanan Diterima!</h2>
        </div>
        
        <p>Halo, <strong>{{ $order->customer_name }}</strong>!</p>
        <p>Terima kasih telah melakukan pemesanan. Data Anda dan bukti pembayaran telah kami terima dan sedang dalam proses verifikasi admin.</p>

        <div class="details">
            <h3>Rincian Pesanan #{{ $order->id }}</h3>
            <table>
                <thead>
                    <tr>
                        <th>Menu</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = 0; @endphp
                    @foreach($order->orderItems as $item)
                    <tr>
                        <td>{{ $item->product_name_snapshot }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rp {{ number_format($item->price_snapshot) }}</td>
                        <td>Rp {{ number_format($item->subtotal) }}</td>
                    </tr>
                    @php $total += $item->subtotal; @endphp
                    @endforeach
                </tbody>
            </table>
            
            <p class="total" style="text-align: right; margin-top: 15px;">
                Total Bayar: Rp {{ number_format($total) }}
            </p>
        </div>

        <p><strong>Informasi Selanjutnya:</strong><br>
        Mohon tunggu notifikasi selanjutnya. Jika pembayaran valid, kami akan mengirimkan email konfirmasi "LUNAS".</p>

        <div class="footer">
            &copy; {{ date('Y') }} Dapur Enak PO System.<br>
            Jangan balas email ini (No-Reply).
        </div>
    </div>
</body>
</html>