<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0; }
        .header { background: #2563eb; color: white; padding: 20px; text-align: center; } /* Biru */
        .content { padding: 20px; color: #333; line-height: 1.6; text-align: center; }
        .btn { background: #2563eb; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; margin-top: 10px; }
        .footer { background: #f9fafb; padding: 15px; text-align: center; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin:0;">Laporan Harian</h2>
        </div>
        <div class="content">
            <h1 style="font-size: 40px; margin: 10px 0; color: #2563eb;">{{ $pendingCount }}</h1>
            <p><strong>Pesanan Menunggu Verifikasi</strong><br>pada Batch: {{ $batchName }}</p>
            
            <p style="margin-bottom: 25px;">Mohon segera login dan cek bukti transfer pembayaran.</p>
            
            <a href="{{ route('login') }}" class="btn">Buka Dashboard Admin</a>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} PO Genta<br>
        </div>
    </div>
</body>
</html>